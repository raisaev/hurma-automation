<?php

declare(strict_types=1);

namespace App\Controller;

use App\Hurma\CoinProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly CoinProcessor $coinProcessor,
        private readonly LoggerInterface $logger,
        #[Autowire(env: 'GOOGLE_SHEET_ID')] private readonly string $sheetId,
        #[Autowire(env: 'GOOGLE_SHEET_NAME')] private readonly string $sheetName,
        #[Autowire(env: 'GOOGLE_SHEET_RANGE')] private readonly string $range,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        /** @var FlashBagAwareSessionInterface $session */
        $session = $request->getSession();

        $content = $this->render(
            'index.html.twig',
            [
                'pageTitle' => 'Hurma: automated Kate',
                'vncUrl'    => $this->parameterBag->get('app.vnc.url'),
                'form'      => [
                    'sheetId'   => $session->get('sheetId', $this->sheetId),
                    'sheetName' => $session->get('sheetName', $this->sheetName),
                    'range'     => $session->get('range', $this->range),
                ],
            ]
        );
        $session->remove('result');

        return $content;
    }

    #[Route('/submit', name: 'submit', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        /** @var FlashBagAwareSessionInterface $session */
        $session = $request->getSession();

        $session->set('sheetId', $request->request->get('sheetId'));
        $session->set('sheetName', $request->request->get('sheetName'));
        $session->set('range', $request->request->get('range'));

        try {
            $result = $this->coinProcessor->process(
                (string) $session->get('sheetId'),
                $request->request->has('sheetName') ? $request->request->getString('sheetName') : null,
                $request->request->getString('range'),
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
            $this->logger->error($e);
            $session->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirectToRoute('index');
    }
}
