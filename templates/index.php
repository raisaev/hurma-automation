<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Templating\PhpEngine;

/** @var PhpEngine $view */
/** @var Kernel $kernel */

?>

<html lang="en">
<head>
    <title><?= $title ?? '' ?></title>
    <link rel="icon" href="<?= $kernel->getBaseUrl('assets/hurma.png') ?>" type="image/jpg">

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">

    <style>
        * {
            font-family: Roboto, Tahoma, Verdana, Segoe, monospace;;
        }
        html {
            position: relative;
            min-height: 100%;
        }
        body {
            margin-bottom: 60px; /* Margin bottom by footer height */
        }
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 60px; /* Set the fixed height of the footer here */
            line-height: 60px; /* Vertically center the text there */
            background-color: rgba(201, 199, 199, 0.75);
        }
    </style>

    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
</head>

<body>

<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#"><?= $title ?? '' ?></a>
</header>

<?php foreach ($messages ?? [] as $type => $byType) : ?>
    <?php foreach ($byType as $message) : ?>
        <div class="alert alert-<?= $type ?>" role="alert"><?= $message ?></div>
    <?php endforeach; ?>
<?php endforeach; ?>

<main role="main" class="container">

    <h1 class="mt-5 mb-5">
        <img src="<?= $kernel->getBaseUrl('assets/hurma.png') ?>" style="width: 30px;">
        hurma is ready for mission
        <a href="<?= $kernel->getVncUrl() ?>" target="_blank" class="badge badge-dark .float-right" data-toggle="tooltip" data-placement="bottom" style="font-size: 10px;">progress</a>
    </h1>

    <form method="post" id="form" action="<?= $kernel->getBaseUrl('submit') ?>">
        <label for="spreadsheetId" class="form-label">Sheet ID</label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon3">https://docs.google.com/spreadsheets/d/</span>
            <input type="text" class="form-control" id="spreadsheetId" name="spreadsheetId" placeholder="{sheet-id}" value="<?= $spreadsheetId ?? '' ?>" aria-describedby="basic-addon3" required>
            <span class="input-group-text">
                <a target="_blank" href="#" onclick="let url = $('#spreadsheetId').val(); if (url === '') return false; window.open('https://docs.google.com/spreadsheets/d/' + url)" class="text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                      <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                    </svg>
                <a>
            </span>
        </div>
        <label class="form-label">Name / Range</label>
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="{sheet-name}" name="sheetName" value="<?= $sheetName ?? '' ?>" aria-label="Name">
            <span class="input-group-text">!</span>
            <input type="text" class="form-control" placeholder="A1:D200" name="range" aria-label="Range" value="<?= $range ?? '' ?>" required>
        </div>
        <div class="form-check d-none">
            <input class="form-check-input" type="checkbox" value="1" id="dryRun" name="dryRun">
        </div>

        <button type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="bottom" title="do not make changes to hurma, just parse and search" onclick="$('#dryRun').prop('checked', true); return true;">dry run</button>
        <button type="submit" class="btn btn-secondary ml-3" onclick="$('#dryRun').prop('checked', false);">process</button>
    </form>

    <?php if (!empty($result)) : ?>
    <h2 class="mt-5 text-primary">results</h2>
    <div>
        <table class="table table-striped">
            <thead class="table-dark">
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($result as $name => $status) : ?>
                <tr>
                    <td><?= $name ?></td>
                    <td><?= $status ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>

<footer class="footer">
    <div class="container" style="text-align: center;">
        <span class="text-muted">Automated Kate. Was born in 1996, automated in 2021 😀</span>
    </div>
</footer>

</body>
</html>
