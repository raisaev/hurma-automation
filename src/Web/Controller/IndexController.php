<?php

declare(strict_types=1);

namespace App\Web\Controller;

use App\Hurma\CoinProcessor;
use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

class IndexController
{
    public function __construct(
        private readonly Kernel $kernel,
        private readonly CoinProcessor $coinProcessor,
    ) {
    }

    // ----------------------------------------

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        /** @var FlashBagAwareSessionInterface $session */
        $session = $request->getSession();

        $templating = new PhpEngine(
            new TemplateNameParser(),
            new FilesystemLoader("{$this->kernel->getTemplatesDir()}/%name%")
        );
        $templating->set(new SlotsHelper());

        $content = $templating->render(
            'index.php',
            [
                'title'         => 'Hurma: automated Kate',
                'kernel'        => $this->kernel,
                'spreadsheetId' => $session->get('spreadsheetId'),
                'sheetName'     => $session->get('sheetName'),
                'range'         => $session->get('range'),
                'messages'      => $session->getFlashBag()->all(),
                'result'        => $request->getSession()->get('result', []),
            ]
        );
        $session->remove('result');

        return (new Response())->setContent($content);
    }

    #[Route('/submit', name: 'submit', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        /** @var FlashBagAwareSessionInterface $session */
        $session = $request->getSession();

        $session->set('spreadsheetId', $request->request->get('spreadsheetId'));
        $session->set('sheetName', $request->request->get('sheetName'));
        $session->set('range', $request->request->get('range'));

        try {
            $result = $this->coinProcessor->process(
                (string)$session->get('spreadsheetId'),
                $request->request->get('sheetName'),
                (string)$request->request->get('range'),
                $request->request->getBoolean('dryRun')
            );

            $session->set('result', $result);
            $session->getFlashBag()->add(
                'success',
                sprintf(
                    'Processed %d records. [dry-mode: %s]',
                    count($result),
                    $request->request->getBoolean('dryRun') ? 'enabled' : 'disabled'
                )
            );
        } catch (\Throwable $e) {
            $session->getFlashBag()->add('danger', $e->getMessage());
        }

        return new RedirectResponse($this->kernel->getBaseUrl());
    }
}
