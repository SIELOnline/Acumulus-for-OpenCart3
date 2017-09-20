<?= $header; ?><?= $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-account" data-toggle="tooltip" title="<?= $button_save; ?>" class="btn btn-primary"><i class="fa <?= $button_icon ?>"></i></button>
        <!--suppress HtmlUnknownTarget -->
        <a href="<?= $cancel; ?>" data-toggle="tooltip" title="<?= $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?= $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><!--suppress HtmlUnknownTarget --> <a href="<?= $breadcrumb['href']; ?>"><?= $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php foreach ($success_messages as $message) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= $message; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php }
        foreach ($warning_messages as $message) { ?>
    <div class="alert alert-warning"><i class="fa fa-warning"></i> <?= $message; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php }
        foreach ($error_messages as $message) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?= $message; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?= $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <!--suppress HtmlUnknownTarget -->
        <form action="<?= $action; ?>" method="post" enctype="multipart/form-data" id="form-acumulus" class="form-horizontal">
          <?= $formRenderer->render($form) ?>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $footer; ?>
