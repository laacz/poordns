<!DOCTYPE html>
<html lang="en">
<head>
    <title>PowerDNS</title>
    <meta charset="utf-8">
    <style>
        table tr:hover td {
            /*background-color: #000;*/
        }

        table tr:hover input {
            border-color: #000;
        }

        <?php
        require('bulma.css');
        ?>
    </style>

</head>
<body>
<div class="container">
    <h1 class="title"><a href="<?=uri('')?>">PoorDNS</a></h1>

    <?php if (count(errors())) { ?>
        <div class="notification is-light is-danger">
            <?php foreach (errors() as $error) { ?>
                <p><?=$error?></p>
            <?php } ?>
        </div>
    <?php } ?>

    <div id="content">
        <form action="">
            <input type="hidden" name="page" value="search">
            <div class="field has-addons">
                <div class="control is-expanded">
                    <input type="search" class="input" name="q" value="<?=htmlspecialchars($_GET['q'] ?? '')?>"
                           placeholder="Search for anything..."/>
                </div>
                <div class="control">
                    <button type="submit" class="button is-outlined is-info">Search</button>
                </div>
            </div>
        </form>
