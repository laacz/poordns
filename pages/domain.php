<?php /** @var Domain $domain */ ?>
<?php /** @var int $default_ttl */ ?>
<?php require('header.php'); ?>
<h2 class="subtitle">Domain <?=$domain->name?></h2>

<form action="" method="post" onsubmit="return(confirm('really delete this domain?'))">
    <input type="hidden" name="action" value="delete-domain">
    <input type="hidden" name="id" value="<?=$domain->id?>">
    <input type="submit" value="Delete domain..." class="button is-outlined is-danger is-small">
</form>

<hr>

<form action="" method="post">
    <input type="hidden" name="action" value="save-domain">
    <input type="hidden" name="domain_id" value="<?=$domain->id?>">
    <input type="hidden" name="record_id" value="">
    <?php foreach ($domain->records() as $record) { ?>
        <?php
        $disabled = $record->type === 'SOA' ? 'disabled' : '';
        ?>
        <div class="field has-addons">
            <div class="control">
                <button class="button is-static is-small" <?=$disabled?>><?=$record->id?></button>
            </div>
            <div class="control">
                <span class="select is-small <?=$disabled?'is-disabled':''?>">
                <select name="type[<?=$record->id?>]" <?=$disabled?>>
                    <?php foreach (recordTypes() as $type) { ?>
                        <option value="<?=$type?>" <?=$record->type === $type ? 'selected' : ''?>><?=$type?></option>
                    <?php } ?>
                </select>
                    </span>
            </div>
            <div class="control">
                <input type="text" name="name[<?=$record->id?>]" class="input is-small"
                       value="<?=htmlspecialchars($record->name)?>" <?=$disabled?> >
            </div>
            <div class="control is-expanded">
                <input type="text" name="content[<?=$record->id?>]" class="input is-small"
                       value="<?=htmlspecialchars($record->content)?>" <?=$disabled?> size="50">
            </div>
            <div class="control">
                <input type="text" name="ttl[<?=$record->id?>]" class="input is-small"
                       value="<?=htmlspecialchars($record->ttl)?>" <?=$disabled?> >
            </div>
            <?php if ($record->type === 'MX') { ?>
                <div class="control">
                    <input type="text" name="prio[<?=$record->id?>]" class="input is-small"
                           value="<?=htmlspecialchars($record->prio)?>" <?=$disabled?> >
                </div>
            <?php } else { ?>
                <div class="control">
                    <input type="text" class="input is-small" disabled>
                </div>
            <?php } ?>
            <?php if ($record->type !== 'SOA') { ?>
                <div class="control">
                    <button type="submit" name="action" value="delete-record"
                            class="button is-small is-danger is-outlined"
                            onclick="return deleteRecord(<?=$record->id?>)">Delete...
                    </button>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    <div class="field">
        <div class="control">
            <input type="submit" value="Save changes" class="button is-small is-success is-outlined">
        </div>
    </div>

    <div class="field has-addons">
        <div class="control">
            <span class="select is-small">
                <select name="new_type">
                    <option value="" disabled>type...</option>
                    <?php foreach (recordTypes() as $type) { ?>
                        <option value="<?=$type?>"><?=$type?></option>
                    <?php } ?>
                </select>
            </span>
        </div>
        <div class="control">
            <input type="text" name="new_name" value="" class="input is-small" placeholder="name">
        </div>
        <div class="control">
            <button class="button is-small is-static">.<?=$domain->name?></button>
        </div>
        <div class="control">
            <input type="text" name="new_content" value="" size="50" class="input is-small" placeholder="content">
        </div>
        <div class="control">
            <input type="text" name="new_ttl" value="" placeholder="<?=$default_ttl?>" class="input is-small">
        </div>
        <div class="control">
            <input type="text" name="new_prio" value="" class="input is-small" placeholder="priority">
        </div>
        <div class="control">
            <button type="submit" name="action" value="add_record" class="button is-small is-success is-outlined">Add record</button>
        </div>
    </div>
</form>

<script>
    function deleteRecord(id) {
        if (!confirm('really delete record ' + id + '?')) {
            return false;
        }
        document.querySelector('input[name="record_id"]').value = id;
        return true;
    }
</script>
<?php require('footer.php'); ?>
