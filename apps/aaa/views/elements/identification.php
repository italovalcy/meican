<?php $user = $argsToElement; ?>

<h1><?php echo _('Information'); ?></h1>

<table class="withoutBorder add">
    <tr>
        <th class="right">
            <?php echo _('Name'); ?>:
        </th>
        
        <td class="left">
            <input type="text" size="50" name="usr_name" value="<?php if ($user) echo $user->usr_name; ?>">
        </td>
    </tr>
    <tr>
        <th class="right">
            <?php echo _('E-mail'); ?>:
        </th>
        <td class="left">
            <input type="text" size="50" name="usr_email" value="<?php if ($user) echo $user->usr_email; ?>">
        </td>
    </tr>
</table>