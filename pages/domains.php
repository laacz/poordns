<?php /** @var Array<Domain> $domains */ ?>
<?php require('header.php'); ?>
    <h2 class="subtitle">Domains</h2>

    <form action="" method="post">
        <div class="field has-addons">
            <input type="hidden" name="action" value="add-domain">
            <div class="control">
                <input type="text" name="name" value="" placeholder="new-domain.tld..."
                       class="input is-small">
            </div>
            <div class="control">
                <input type="submit" value="Add domain" class="button is-small is-outlined is-success">
            </div>
        </div>
    </form>

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
        </tbody>
    </table>
<?php require('footer.php'); ?>