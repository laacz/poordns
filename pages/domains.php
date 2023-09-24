<?php /** @var Array<Domain> $domains */ ?>
<?php require('header.php'); ?>
    <h2 class="subtitle">Domains</h2>
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
                    <a href="<?=uri('domain', array('id' => $domain->id))?>"><?=$domain->name?></a>
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