<div id="headerbar">
    <h1><?php echo lang('email_templates'); ?></h1>

    <div class="pull-right">
        <a class="btn btn-sm btn-primary" href="<?php echo site_url('email_templates/form'); ?>">
            <i class="fa fa-plus"></i> <?php echo lang('new'); ?>
        </a>
    </div>

    <div class="pull-right">
        <?php echo pager(site_url('email_templates/index'), 'mdl_email_templates'); ?>
    </div>

</div>

<div id="content" class="table-content">

    <?php echo $this->layout->load_view('layout/alerts'); ?>

    <table class="table table-striped">

        <thead>
        <tr>
            <th><?php echo lang('title'); ?></th>
            <th><?php echo lang('type'); ?></th>
            <th><?php echo lang('options'); ?></th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($email_templates as $email_template) { ?>
            <tr>
                <td><?php echo $email_template->email_template_title; ?></td>
                <td><?php echo lang($email_template->email_template_type); ?></td>
                <td>
                    <div class="options btn-group">
                        <a class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" href="#"><i
                                class="fa fa-cog"></i> <?php echo lang('options'); ?></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="<?php echo site_url('email_templates/form/' . $email_template->email_template_id); ?>">
                                    <i class="fa fa-edit fa-margin"></i> <?php echo lang('edit'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo site_url('email_templates/delete/' . $email_template->email_template_id); ?>"
                                   onclick="return confirm('<?php echo lang('delete_record_warning'); ?>');">
                                    <i class="fa fa-trash-o fa-margin"></i> <?php echo lang('delete'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>

    </table>

</div>