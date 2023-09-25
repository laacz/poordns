<?php /** @var Array<Domain> $domains */ ?>
<?php require('header.php'); ?>
    <h2 class="subtitle">Search for results</h2>
    <table class="table is-narrow is-hoverable">
        <thead>
        <tr>
            <th>Domain</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($domains as $domain) { ?>
            <tr>
                <td>
                    <a href="<?=uri('domain', array('id' => $domain->id))?>"><?=hl($domain->name, $_GET['q']??'')?></a>
                </td>
                <td>
                    <?php
                    $seen = [];
                    foreach ($domain->records() as $record) {
                        $r = false;
                        if (str_contains(strtolower($record->name), strtolower($_GET['q']??''))) {
                            $r =  hl($record->name, $_GET['q']??'') . ' ';
                        } else if (str_contains(strtolower($record->content), strtolower($_GET['q']??''))) {
                            $r = hl($record->content, $_GET['q']??'') . ' ';
                        }
                        if ($r) {
                            $seen[$r] = $r;
                        }
                    }

                    echo implode(', ', $seen);
                    ?>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td>
                <form action="" method="post">
                    <input type="hidden" name="action" value="add-domain">
                    <input type="text" name="name" value="" placeholder="new-domain.tld..." class="input is-small">
                    <input type="submit" value="Add domain" class="button is-small is-outlined is-success">
                </form>
            </td>
        </tr>
        </tbody>
    </table>
<?php require('footer.php'); ?>