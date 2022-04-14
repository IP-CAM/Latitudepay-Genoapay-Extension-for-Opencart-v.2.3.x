<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">

  <ul class="breadcrumb">
	<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
	<?php } ?>
  </ul>

	<!--------------------------
		ERROR HANDLING START
	--------------------------->

	<?php if ($attention) { ?>
		<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $attention; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	<?php } ?>

	<?php if ($success) { ?>
		<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	<?php } ?>

	<?php if ($error_warning) { ?>
		<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
	<?php } ?>

	<!--------------------------
		ERROR HANDLING END
	--------------------------->

  <div class="page-header">
	<div class="container-fluid">
	  <div class="pull-right">
		<button type="submit" form="form-latitudepay" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
		<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
	  <h1><i class="fa fa-credit-card"></i> <?php echo $heading_title; ?></h1>
	</div>
  </div>

  <div class="container-fluid">
	<div class="panel-body">
	  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-latitudepay" class="form-horizontal">

		<ul class="nav nav-tabs">
		  <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_settings; ?></a></li>
		  <li><a href="#tab-order-status" data-toggle="tab"><?php echo $tab_order_status; ?></a></li>
		</ul>

		<div class="tab-content">


<!-----------------------------------------------
-------------------------------------------------
SETTINGS TAB
-------------------------------------------------
------------------------------------------------>

		
		  <div class="tab-pane active" id="tab-general">

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-version">Version</label>
			  <div class="col-sm-10">
				<input type="text" readonly name="latitudepay_version" value="1.0.5" id="input-version" class="form-control" />
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
			  <div class="col-sm-10">
				<select name="latitudepay_status" id="input-status" class="form-control">
				  <?php if ($latitudepay_status) { ?>
					  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
					  <option value="0"><?php echo $text_disabled; ?></option>
				  <?php } else { ?>
					  <option value="1"><?php echo $text_enabled; ?></option>
					  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
				  <?php } ?>
				</select>
			  </div>
			</div>
			
			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
			  <div class="col-sm-10">
				<input type="text" name="latitudepay_sort_order" value="<?php echo $latitudepay_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-environment"><?php echo $entry_environment; ?></label>
			  <div class="col-sm-10">
				<select name="latitudepay_environment" id="input-environment" class="form-control">
				  <?php if ($latitudepay_environment) { ?>
					  <option value="1" selected="selected"><?php echo $text_production; ?></option>
					  <option value="0"><?php echo $text_sandbox; ?></option>
				  <?php } else { ?>
					  <option value="1"><?php echo $text_production; ?></option>
					  <option value="0" selected="selected"><?php echo $text_sandbox; ?></option>
				  <?php } ?>
				</select>
			  </div>
			</div>
			
			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-production-api-key">Production API Key</label>
			  <div class="col-sm-10">
				<input type="text" name="latitudepay_production_api_key" value="<?php echo $latitudepay_production_api_key; ?>" placeholder="Production API Key" id="input-production-api-key" class="form-control" />
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-production-api-secret">Production API Secret</label>
			  <div class="col-sm-10">
				<input type="text" name="latitudepay_production_api_secret" value="<?php echo $latitudepay_production_api_secret; ?>" placeholder="Production API Secret" id="input-production-api-secret" class="form-control" />
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-sandbox-api-key">Sandbox API Key</label>
			  <div class="col-sm-10">
				<input type="text" name="latitudepay_sandbox_api_key" value="<?php echo $latitudepay_sandbox_api_key; ?>" placeholder="Sandbox API Key" id="input-sandbox-api-key" class="form-control" />
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-sandbox-api-secret">Sandbox API Secret</label>
			  <div class="col-sm-10">
				<input type="text" name="latitudepay_sandbox_api_secret" value="<?php echo $latitudepay_sandbox_api_secret; ?>" placeholder="Sandbox API Secret" id="input-sandbox-api-secret" class="form-control" />
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-minimum-total">Minimum Total ($)</label>
			  <div class="col-sm-10">
				  <?php if ($latitudepay_minimum_total) { ?>
					<input type="text" readonly name="latitudepay_minimum_total" value="<?php echo $latitudepay_minimum_total; ?>" placeholder="Minimum Total" id="input-minimum-total" class="form-control" />
				  <?php } else { ?>
					<input type="text" readonly name="latitudepay_minimum_total" value="20" placeholder="Minimum Total" id="input-minimum-total" class="form-control" />
				  <?php } ?>
				<span class="help-block"><?php echo $help_total; ?></span>
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-configuration-last-update">Configuration Last Update (UTC)</label>
			  <div class="col-sm-10">
				  <?php if ($latitudepay_configuration_last_update) { ?>
					<input type="text" readonly name="latitudepay_configuration_last_update" value="<?php echo $latitudepay_configuration_last_update; ?>" placeholder="Configuration Last Update (UTC)" id="input-configuration-last-update" class="form-control" />
				  <?php } else { ?>
					<input type="text" readonly name="latitudepay_configuration_last_update" value="2021-01-01 00:00:00" placeholder="Configuration Last Update (UTC)" id="input-configuration-last-update" class="form-control" />
				  <?php } ?>
				<span class="help-block">This is updated automatically during checkout session (once every 24 hours).</span>
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-debug"><?php echo $entry_debug; ?></label>
			  <div class="col-sm-10">
				<select name="latitudepay_debug" id="input-debug" class="form-control">
				  <?php if ($latitudepay_debug) { ?>
					  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
					  <option value="0"><?php echo $text_disabled; ?></option>
				  <?php } else { ?>
					  <option value="1"><?php echo $text_enabled; ?></option>
					  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
				  <?php } ?>
				</select>
				<span class="help-block"><?php echo $help_debug; ?></span>
			  </div>
			</div>

		  </div>


<!-----------------------------------------------
-------------------------------------------------
ORDER STATUS TAB
-------------------------------------------------
------------------------------------------------>


		  <div class="tab-pane" id="tab-order-status">

		  <div class="form-group">
			  <label class="col-sm-2 control-label"><?php echo $entry_success_status; ?></label>
			  <div class="col-sm-10">
				<select name="latitudepay_entry_success_status_id" class="form-control">
				  <?php foreach ($order_statuses as $order_status) { ?>
					  <?php if ($order_status['order_status_id'] == $latitudepay_entry_success_status_id) { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label"><?php echo $entry_pending_status; ?></label>
			  <div class="col-sm-10">
				<select name="latitudepay_entry_pending_status_id" class="form-control">
				  <?php foreach ($order_statuses as $order_status) { ?>
					  <?php if ($order_status['order_status_id'] == $latitudepay_entry_pending_status_id) { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label"><?php echo $entry_failed_status; ?></label>
			  <div class="col-sm-10">
				<select name="latitudepay_entry_failed_status_id" class="form-control">
				  <?php foreach ($order_statuses as $order_status) { ?>
					  <?php if ($order_status['order_status_id'] == $latitudepay_entry_failed_status_id) { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label"><?php echo $entry_refunded_status; ?></label>
			  <div class="col-sm-10">
				<select name="latitudepay_entry_refunded_status_id" class="form-control">
				  <?php foreach ($order_statuses as $order_status) { ?>
					  <?php if ($order_status['order_status_id'] == $latitudepay_entry_refunded_status_id) { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label"><?php echo $entry_partially_refunded_status; ?></label>
			  <div class="col-sm-10">
				<select name="latitudepay_entry_partially_refunded_status_id" class="form-control">
				  <?php foreach ($order_statuses as $order_status) { ?>
					  <?php if ($order_status['order_status_id'] == $latitudepay_entry_partially_refunded_status_id) { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
			  </div>
			</div>

		  </div>

		</div>
	  </form>
	</div>
  </div>
</div>
<?php echo $footer; ?>