<style>
.prescriber-actions-wrapper {
	padding: 18px;
	border: 1px solid #dbe3ef;
	border-radius: 14px;
	background: #fff;
	box-shadow: 0 8px 30px rgba(16, 33, 63, 0.06);
}
.prescriber-action-btns .button {
	margin-right: 10px;
	border-radius: 999px;
	padding: 4px 18px !important;
}
.prescriber-action-btns .button-error, .bg-error {
	background: #dd3c3c;
	border-color: #dd3c3c;
	color: #fff;
}
.prescriber-action-btns .button-info, .bg-info {
	background: #e8f1ff;
	border-color: #c8dafb;
	color: #000;
}
.presc-ingredients-modal {
	position: fixed;
	width: 100%;
	height: 100%;
	top: 0;
	bottom: 0;
	left: 0;
	z-index: 9;
}
.presc-ingredients-modal:before {
	position: absolute;
	content: '';
	width: 100%;
	height: 100%;
	background: #000;
	opacity: 0.2;
}
.presc-ingredients-modal .modal-body {
	position: relative;
	z-index: 999999;
	background: #fff;
	width: 60%;
	height: 80%;
	margin: 0 auto;
	margin-top: 50px;
	bottom: 0;
	overflow: hidden;
	max-height: 700px;
}
.presc-ingredients-modal .modal-body .modal-content {
	overflow-y: auto;
	max-height: 400px;
	padding: 50px;
}
.presc-ingredients-modal .modal-body .modal-close {
	position: absolute;
	right: 0;
	top: -15px;
	font-size: 30px;
}
.presc-ingredients-modal .modal-body .modal-close button {
	border: none;
	background: transparent;
}
.presc-ingredients-modal .modal-content .ingred-container .input-container {
	display: flex;
	align-items: end;
}
.presc-ingredients-modal .modal-content select {
	width: 100%;
}
.presc-ingredients-modal .modal-content .modal-action-container {
	width: 50%;
	text-align: right;
}
.modal-action {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 20px;
	position: absolute;
	bottom: 0;
	left: 0;
	right: 0;
	box-shadow: 0 0 10px #ccc;
	background: #fff;
}
.modal-action button {
	margin-right: 10px !important;
}
#prescription-ing-table {
	margin-top: 20px;
}
.presc-ajax-response {
	padding: 10px;
	margin: 10px 0;
	background: #fff2f2;
	border: 1px solid #ffd7d7;
	border-radius: 10px;
}
.presc-ajax-response p {
	margin: 0;
	font-size: 15px;
}
</style>
<?php
	$status = get_post_meta($post->ID, '_user_active_status', true);
?>
<div class="prescriber-actions-wrapper">
	<div class="presc-ajax-response" style="display:none;"></div>
	<p class="prescriber-action-btns" style ="text-align:right;">
		<button class="button button-primary button-large approve-me process-btn"><?=$status && $status == 'approved' ? 'Re-Approve' : 'Approve'?></button>
		<a class="button button-error button-large reject-me process-btn" href = "#">Reject</a>
	</p>
	<div class="error-response" hidden style="margin-top: 50px;"></div>
</div>

<script>
jQuery(document).ready(function($) {
	var parentWrapper = $('.prescriber-actions-wrapper'), approveAction = parentWrapper.find('.approve-me:not(disabled)'),
		rejectAction = parentWrapper.find('.reject-me:not(.disabled)');
	
	//this is to finally approve the prescriber once presc has been added
	approveAction.click(function(e) {
		e.preventDefault();
		if(confirm('Approve prescriber?')) {
			if(parentWrapper.find('input[name=approve_prescriber]').length) {
				parentWrapper.find('input[name=approve_prescriber]').remove();
			}
			parentWrapper.append('<input type = "hidden" name="approve_prescriber" value = "1" />');
			$('.approve-me').addClass('disabled');
			var formData = new FormData();
			formData.append('post_id','<?=$post->ID?>');
			formData.append('approve_prescriber', '1');
			formData.append('action', 'prescriber_action');
			performPrescriberAction(parentWrapper, formData);
		}
	});
	
	
	rejectAction.click(function(e) {
		e.preventDefault();
		if(confirm('Reject this Prescriber?')) {
			if(parentWrapper.find('input[name=reject_prescriber]').length) {
				parentWrapper.find('input[name=reject_prescriber]').remove();
			}
			parentWrapper.append('<input type = "hidden" name="reject_prescriber" value = "1" />');
			var formData = new FormData();
			formData.append('post_id', '<?=$post->ID?>');
			formData.append('reject_prescriber', 1);
			formData.append('action', 'prescriber_action');
			performPrescriberAction(parentWrapper, formData);
		}
	});
	
	function performPrescriberAction(parentWrapper, data) {
		$('.process-btn').addClass('disabled');
		$.ajax({
			url: '<?php echo admin_url("admin-ajax.php")?>',
			type: 'POST',
			data,
			processData: false,
			contentType: false,
			success: function(res) {
				clearDisabledBtn(parentWrapper);
				if(res.success == 1) {
					window.location.reload();
				}
			},
			error: function(err) {
				clearDisabledBtn(parentWrapper);
				
				if(typeof err.responseJSON != 'undefined' && typeof err.responseJSON.message != 'undefined') {
					prescAjaxResp(parentWrapper, '<p>'+error.responseJSON.message+'</p>');
				} else {
					prescAjaxResp(parentWrapper, '<p>Something went wrong!</p>');
				}
			}
		});
	}
	
	function prescAjaxResp(parentWrapper, data) {
		parentWrapper.find('.presc-ajax-response').show();
		parentWrapper.find('.presc-ajax-response').html(data);
	}
	
	function clearDisabledBtn(parentWrapper) {
		parentWrapper.find('.approve-me').removeClass('disabled');
		parentWrapper.find('.process-btn').removeClass('disabled');
	}
});
</script>