var wt_iew_import=(function( $ ) {
	//'use strict';
	var wt_iew_import=
	{
		ajax_data:{},
		selected_template:0,
		selected_template_name:'',
		to_import:'',
		to_import_title:'',
		import_method:'',
		current_step:'',
		loaded_status_arr:{'loaded':1, 'loading':2, 'not_loaded':0},
		page_overlay:false,
		step_keys:[],
		form_data:{},
		is_valid_file:false,
		temp_import_file:'',
		file_from:'',
		blocked_action:'', /* the current action will be blocked if file validation is not done. After the successful validation the blocked action will be executed */
		local_import_file:'',
		url_import_file:'',
		on_rerun:false,
                import_finished:false,
		rerun_id:0,
                cron_edit_id:0,
                import_delimiter:',',
                import_start_time:0,
		Set:function()
		{
			this.step_keys=Object.keys(wt_iew_import_params.steps);
			if(this.on_rerun)
			{
				this.rerun();
			}else
			{
				var first_step=this.step_keys[0];
				this.load_steps([first_step], first_step);
				this.show_step_page(first_step, false); /* just to show a loading */
			}
		},
		rerun:function()
		{
			var last_step=this.step_keys[this.step_keys.length-1];
			this.load_steps(this.step_keys, last_step);
			this.show_step_page(last_step, false); /* just to show a loading */
		},
		filter_loaded_steps:function(steps)
		{
			var filtered_steps=new Array();
			$.each(steps, function(step_ind, step){  
			  	if(wt_iew_import.get_page_dom_object(step).attr('data-loaded')!=wt_iew_import.loaded_status_arr['loaded'])
			  	{
			  		filtered_steps.push(step);
			  	}
			});
			return filtered_steps;
		},
		load_steps:function(steps, step_to_show)
		{
			steps=this.filter_loaded_steps(steps);
			if(steps.length==0){ return; }
			this.prepare_ajax_data('get_steps', 'json');
			this.ajax_data.steps=steps;
			if(this.on_rerun)
			{
				this.ajax_data.rerun_id=this.rerun_id;
                                this.ajax_data.cron_edit_id=this.cron_edit_id;
			}
			this.set_step_loading_status(steps, 'loading');
			this.set_ajax_page_loader(steps, 'loading');
			$.ajax({
				type: 'POST',
                                url:wt_iew_params.ajax_url,
                                data:this.ajax_data,
                                dataType:'json',
                                success:function(data)
				{
					if(data!=null && data.status==1)
					{
						wt_iew_import.set_step_page(data);
						wt_iew_import.set_step_loading_status(steps, 'loaded');
						if(step_to_show)
						{
							wt_iew_import.show_step_page(step_to_show, true);
						}
						if(wt_iew_import.on_rerun)
						{
							wt_iew_import.load_meta_mapping_fields();
							wt_iew_import.set_validate_file_info(); /* this will prevent revalidation of files */
							wt_iew_import.on_rerun=false;
							wt_iew_import.rerun_id=0;
						}
					}else
					{
						wt_iew_import.set_step_loading_status(steps, 'not_loaded');
						wt_iew_import.set_ajax_page_loader(steps, 'error');
					}
					wt_iew_import.remove_ajax_page_loader();
				},
				error:function()
				{
					wt_iew_import.set_step_loading_status(steps, 'not_loaded');
					wt_iew_import.remove_ajax_page_loader();
					wt_iew_import.set_ajax_page_loader(steps, 'error');
				}
			});
		},
		load_meta_mapping_fields:function()
		{
		    	if(heavy_meta.status){
			    $('.wt_iew_info_box').after('<div id="heavy_meta_notifier" style="padding:10px;margin:5px 0px;" class="notice heavy_meta_notifier notice-warning is-dismissible"><b>'+heavy_meta.message+'</b></div>');
			}
                        
                        jQuery('.wt_iew_import_step_mapping > .wt-iew-plugin-toolbar').append('<span class="wt-import-meta-loader" style="margin-top: 11px; float:right;">'+wt_iew_params.msgs.loading+'</span>');
                        jQuery('.wt_iew_import_action_btn[data-action="advanced"]').prop('disabled', true);
                        
			this.prepare_ajax_data('get_meta_mapping_fields', 'json');
                        this.ajax_data['rerun_id']=this.rerun_id;
                        this.ajax_data['cron_edit_id']=this.cron_edit_id;
			this.ajax_data['file_head_meta']=JSON.stringify(wt_iew_file_head_remaining_meta);
			$('.meta_mapping_box_con[data-loaded="0"]').html('<div class="wt_iew_import_step_loader">'+wt_iew_params.msgs.loading+'</div>');
			$.ajax({
				type: 'POST',
            	url:wt_iew_params.ajax_url,
            	data:this.ajax_data,
            	dataType:'json',
            	success:function(data)
				{   
                                        jQuery('.wt_iew_import_action_btn[data-action="advanced"]').prop('disabled', false);
                                        jQuery('.wt-import-meta-loader').remove();
                                        
					if(data.status==1)
					{
						if ( data.heavy_meta ) {
						    if($('#heavy_meta_notifier').length == 0){
						    $( '.wt_iew_info_box' ).after( '<div id="heavy_meta_notifier" style="padding:10px;margin:5px 0px;" class="notice heavy_meta_notifier notice-warning is-dismissible"><b>' + data.heavy_meta.message + '</b></div>' );
						}
						}					    
						$.each(data.meta_html, function(meta_id, meta_content){
							$('.meta_mapping_box_con[data-key="'+meta_id+'"]').html(meta_content).attr('data-loaded', 1);					  	
						});
						wt_iew_import.enable_sortable();
						wt_iew_import.reg_mapping_field_bulk_action();
						wt_iew_popover.Set();
                                                                    if(jQuery('.wt_iew_import_action_btn').attr('data-action') == 'advanced'){
                        jQuery('.media-button.wt_iew_import_action_btn').prop('disabled', false);
                    }
					}else
					{
						$('.meta_mapping_box_con[data-loaded="0"]').html('<div class="wt_iew_import_step_loader">'+wt_iew_params.msgs.error+'</div>');
					}
				},
				error:function()
				{
					$('.meta_mapping_box_con[data-loaded="0"]').html('<div class="wt_iew_import_step_loader">'+wt_iew_params.msgs.loading+'</div>');
				}
			});
		},
		console_formdata:function()
		{
			console.log(this.form_data);
		},
		refresh_step:function(no_overlay)
		{
			/* if popover is opened */
			wt_iew_popover.closePop();

			if(!no_overlay){
				this.page_overlay=true; 
			}
			this.reset_step_loaded_state([this.current_step]);
			this.load_steps([this.current_step], this.current_step);
		},
		get_need_to_reload_steps:function()
		{
			var rest_steps=this.step_keys.slice(0);
			/* remove first and second steps */
			rest_steps.shift(); 
			rest_steps.shift();
			return rest_steps;
		},
		load_pending_steps:function(no_overlay, show_step)
		{
			if(!no_overlay){
				this.page_overlay=true; 
			}
			if(!show_step)
			{
				show_step=this.current_step;
			}
			this.load_steps(this.get_need_to_reload_steps(), show_step);
		},
		get_page_dom_object:function(step)
		{
			return $('.wt_iew_import_step_'+step);
		},
		remove_ajax_page_loader:function()
		{
			this.hide_import_info_box();
			$('.wt_iew_overlayed_loader').hide();
			$('.spinner').css({'visibility':'hidden'});	
			this.page_overlay=false;
		},
		set_ajax_page_loader:function(steps, msg_type)
		{
			if(this.page_overlay)
			{
				var h=parseInt($('.wt_iew_import_step_main').outerHeight());
				var w=parseInt($('.wt_iew_import_step_main').outerWidth());
				$('.wt_iew_overlayed_loader').show().css({'height':h,'width':w,'margin-top':'30px','margin-left':'30px'});
				$('.spinner').css({'visibility':'visible'});
			}else
			{
				var msg='';
				if(msg_type=='loading')
				{
					msg=wt_iew_params.msgs.loading;
				}else if(msg_type=='error')
				{
					msg=wt_iew_params.msgs.error;
				}
				$.each(steps, function(step_ind, step){
					wt_iew_import.get_page_dom_object(step).html('<div class="wt_iew_import_step_loader">'+msg+'</div>');
				});
			}		
		},
		hide_import_info_box:function()
		{
			$('.wt_iew_loader_info_box').hide();
		},
		refresh_import_page:function()
		{
                    wt_iew_import.import_finished = true;
                    location.reload();
		},                                 
		set_import_progress_info:function(msg)
		{
			$('.wt_iew_loader_info_box').show().html(msg);
		},
		nonstep_actions:function(action)
		{			
			this.prepare_ajax_data(action, 'json');
			if(action=='save_template' || action=='save_template_as' || action=='update_template')
			{
				$('.wt_iew_template_name_wrn').hide();
				var pop_elm=$('.wt_iew_template_name');
				var popup_label=pop_elm.attr('data-save-label');
				if(action=='save_template_as')
				{
					var popup_label=pop_elm.attr('data-saveas-label');
				}
				pop_elm.find('.wt_iew_popup_hd_label, .wt_iew_template_create_btn').text(popup_label);
				wt_iew_popup.showPopup(pop_elm);
				$('[name="wt_iew_template_name_field"]').val(this.selected_template_name).focus();

				$('.wt_iew_template_create_btn').unbind('click').click(function(){
					var name=$.trim($('.wt_iew_template_name_field').val());
					if(name=='')
					{
						$('.wt_iew_template_name_wrn').show();
						$('.wt_iew_template_name_field').focus();
					}else
					{
						$('.wt_iew_template_name_wrn').hide();
						wt_iew_popup.hidePopup();

						wt_iew_import.prepare_form_data();
						wt_iew_import.ajax_data['template_name']=name;
						wt_iew_import.ajax_data['form_data']=wt_iew_import.form_data;
						wt_iew_import.do_nonstep_action(action);
					}
				});
			}else if(action=='validate_file' || action=='download')
			{
				this.prepare_form_data();
				this.ajax_data['form_data']=this.form_data;
				this.do_nonstep_action(action);
			}else
			{
				/* custom action section for other modules */
				this.prepare_form_data();
				this.ajax_data['form_data']=this.form_data;
				wt_iew_custom_action(this.ajax_data, action, this.selected_template);
			}
		},
		do_nonstep_action:function(action)
		{
			this.page_overlay=true;
			this.set_ajax_page_loader();
			
			if(action=='download')
			{
				wt_iew_import.set_import_progress_info(wt_iew_import_params.msgs.processing_file);
			}
			wt_iew_import.import_ajax_xhr = $.ajax({
				type: 'POST',
            	url:wt_iew_params.ajax_url,
            	data:this.ajax_data,
            	dataType:'json',
				success:function(data)
				{
					wt_iew_import.remove_ajax_page_loader();
					if(wt_iew_import.is_object(data) && data.hasOwnProperty('status'))
					{			
						if(data.status==1)
						{
							if(action=='save_template' || action=='save_template_as' || action=='update_template')
							{
								wt_iew_import.selected_template=data.id;
								wt_iew_import.selected_template_name=data.name;
								//wt_iew_notify_msg.success(wt_iew_params.msgs.success, false);
								wt_iew_notify_msg.success(data.msg, false);
								
							}else if(action=='import')
							{                                                               
            
								if(data.finished==1)
								{
                                                                    wt_iew_import.temp_import_file='';
                                                                    let [firstKey] = Object.keys(data.log_data);
                                                                    if(data.log_data[firstKey].post_link){
                                                                       
                                                                    wt_iew_import.show_import_popup();
                                                                    $('.wt_iew_import_progress_wrap').addClass('open');
                                                                        
                                                                    var newk = 0;
                                                                    var deleted_result = [];
                                                                    if (typeof data.log_data.detele_results !== 'undefined') {
                                                                        deleted_result = data.log_data.detele_results;
                                                                        delete data.log_data.detele_results;
                                                                    }
                                                                    $.each(data.log_data, function (j) {
                                                                                var status_icon = (data.log_data[j].status == true ) ? 'yes-alt' : 'dismiss';
                                                                                var item_name = (data.log_data[j].post_link.edit_url) ?  '<a href="'+data.log_data[j].post_link.edit_url+'" target="_blank">'+data.log_data[j].post_link.title+'</a>' : data.log_data[j].post_link.title;
										$('#wt_iew_import_progress tbody').append( '<tr id="row-' + data.log_data[j].row + '" class="' + data.log_data[j].status + '"><td style="width:15%"><span class="result" title="' + data.log_data[j].row + '">' + data.log_data[j].row + '</span></td><td style="width:20%" class="row">' + item_name + '</td><td style="width:50%">' + data.log_data[j].message + '</td><td style="width:20%" class="reason"><span class="dashicons dashicons-'+status_icon+'"></span></td></tr>' );
                                                                                newk = j;
                                                                    });
                                                                    if(Object.keys(deleted_result).length !== 0){
                                                                      Object.keys(deleted_result).forEach(function(key) {
                                                                                newk++;
                                                                                var item_name = deleted_result[key].post_title;
										$('#wt_iew_import_progress tbody').append( '<tr id="row-' + newk + '" class="deleted"><td style="width:15%"><span class="result" title="' + newk + '">' + newk + '</span></td><td style="width:20%" class="row">' + item_name + '</td><td style="width:50%">' + wt_iew_import_params.msgs.deleted_success + '</td><td style="width:20%" class="reason"><span class="dashicons dashicons-remove"></span></td></tr>' );
                                                                        });  
                                                                    }
                                                                    //$('.wt-iew-importer-progress').val(data.total_percent);
                                                                    var elm = document.getElementsByClassName('progressab')[0];
                                                                    elm.style.width = 100+"%";
                                                                    document.getElementById('row-' + ( newk )).scrollIntoView(false);                                                                  
                                                                    $('.progressa').hide();
                                                                    
                                                                    $('#wt-iew-import-results-total-count').html((data.total_success + data.total_failed));
                                                                    $('#wt-iew-import-results-imported-count').html(data.total_success);
                                                                    $('#wt-iew-import-results-failed-count').html(data.total_failed);
                                                                    
                                                                    $('.wt-iew-import-completed').show();
                                                                    var time_taken = new Date().getTime() - wt_iew_import.import_start_time;
                                                                        time_taken = (time_taken/1000); // convert to seconds
                                                                    $('#wt-iew-import-time-taken').html(wt_iew_import.toHHMMSS(time_taken));
                                                                    $('.wt-iew-import-time').show();
                                                                    $('.wt_iew_view_log_btn').attr('data-log-file', data.log_file);
                                                                    $('.wt_iew_view_log_btn').show();
                                                                    $('.wt_iew_view_imported_items').attr('href', wt_iew_import_params.addons[wt_iew_import.to_import].page_link);
                                                                    $('.wt_iew_view_imported_items').text(wt_iew_import_params.addons[wt_iew_import.to_import].text);
                                                                    $('.wt_iew_view_imported_items').show();                                                                    
                                                                    $('.wt_iew_popup_close_btn').show();
                                                                    $('.wt_iew_popup_cancel_btn').hide();
                                                                }else{
                                                                    wt_iew_notify_msg.success(wt_iew_params.msgs.success, false);
                                                                    wt_iew_import.set_import_progress_info(data.msg);
                                                                }
                                                                wt_iew_import.import_finished = true;
								}
								else
								{
                                                                    let [firstKey] = Object.keys(data.log_data);
                                                                    if(data.log_data[firstKey].post_link){
                                                                    wt_iew_import.show_import_popup();
                                                                    $('.wt_iew_import_progress_wrap').addClass('open');                                                                        
                                                                    var newk = 0;
                                                                    $.each(data.log_data, function (j) {
                                                                                var status_icon = (data.log_data[j].status == true ) ? 'yes-alt' : 'dismiss';
                                                                                var item_name = (data.log_data[j].post_link.edit_url) ?  '<a href="'+data.log_data[j].post_link.edit_url+'" target="_blank">'+data.log_data[j].post_link.title+'</a>' : data.log_data[j].post_link.title;
										$('#wt_iew_import_progress tbody').append( '<tr id="row-' + data.log_data[j].row + '" class="' + data.log_data[j].status + '"><td style="width:15%"><span class="result" title="' + data.log_data[j].row + '">' + data.log_data[j].row + '</span></td><td style="width:20%" class="row">' + item_name + '</td><td style="width:50%">' + data.log_data[j].message + '</td><td style="width:20%" class="reason"><span class="dashicons dashicons-'+status_icon+'"></span></td></tr>' );
                                                                                newk = j;
                                                                    });
                                                                    
var progressval = data.total_percent;
var elm = document.getElementsByClassName('progressab')[0];
elm.style.width = progressval+"%";

elm.innerText = progressval+"%";
elm.style.backgroundColor = '#5FBA89';
                                                                        //$('.wt-iew-importer-progress').val(data.total_percent);
                                                                        document.getElementById('row-' + ( newk )).scrollIntoView({block: 'end', behavior: 'smooth'});
                                                                    }else{
                                                                        wt_iew_import.set_import_progress_info(data.msg);
                                                                    }
									wt_iew_import.ajax_data['offset']=data.new_offset;
									wt_iew_import.ajax_data['import_id']=data.import_id;
									wt_iew_import.ajax_data['total_records']=data.total_records;
									wt_iew_import.ajax_data['offset_count']=data.offset_count;
									wt_iew_import.ajax_data['total_success']=data.total_success;
									wt_iew_import.ajax_data['total_failed']=data.total_failed;
									wt_iew_import.do_nonstep_action(action);
								}
							}else if(action=='download')
							{
                                                                wt_iew_import.show_import_popup();
                                                                $('.wt_iew_import_progress_wrap').addClass('open');
								//wt_iew_import.set_import_progress_info(data.msg);
								wt_iew_import.ajax_data['import_id']=data.import_id;
								wt_iew_import.ajax_data['total_records']=data.total_records;
								if(data.finished==3)/* finished file processing */
								{
                                                                        wt_iew_import.import_start_time = new Date().getTime();
                                                                        $("#wt_iew_import_progress_tbody").empty();
                                                                        //$('#import-start-area').hide();
                                                                        $('.progressa').show();
                                                                        $('.wt-iew-import-completed').hide();
                                                                        $('.wt-iew-import-time').hide();
                                                                        $('.wt_iew_popup_close_btn').hide();
                                                                        $('.wt_iew_view_imported_items').hide();
                                                                        $('.wt_iew_view_log_btn').hide();
                                                                         $('.wt_iew_popup_cancel_btn').show();
									wt_iew_import.ajax_data['offset']=0;
									wt_iew_import.ajax_data['offset_count']=0;
									wt_iew_import.ajax_data['import_action']='import';
									wt_iew_import.ajax_data['temp_import_file']=data.temp_import_file;
									wt_iew_import.temp_import_file=data.temp_import_file;
									wt_iew_import.do_nonstep_action('import');
								}else
								{
									wt_iew_import.ajax_data['offset']=data.new_offset;
									wt_iew_import.ajax_data['import_action']='download';
									wt_iew_import.do_nonstep_action('download');
								}	
							}
							else if(action=='validate_file')
							{
								wt_iew_import.is_valid_file=true;

								/* set meta step status to not loaded */
								wt_iew_import.reset_meta_step_loaded_state();

								wt_iew_import.temp_import_file=data.file_name;
								wt_iew_import.set_validate_file_info();
								if(wt_iew_import.blocked_action!='') /* pending action exists */
								{
									if(wt_iew_import.is_step(wt_iew_import.blocked_action))
									{
										/* load all pending steps, and show the next step (Blocked action) */
										wt_iew_import.load_pending_steps(false, wt_iew_import.blocked_action);

									}else /* may be import(download) */
									{
										wt_iew_import.nonstep_actions(wt_iew_import.blocked_action);
									}

									/* clear the blocked action */
									wt_iew_import.blocked_action='';
								}
							}else
							{

							}
						}else
						{
							if(data.msg!="")
							{
								wt_iew_notify_msg.error(data.msg, false);
							}else
							{
								wt_iew_notify_msg.error(wt_iew_params.msgs.error, false);
							}
						}
					}else
					{
						wt_iew_import.temp_import_file='';
						wt_iew_notify_msg.error(wt_iew_params.msgs.error, false);
					}
				},
				error: function(jqXHR) {
					wt_iew_import.temp_import_file = '';
					wt_iew_import.remove_ajax_page_loader();
					jQuery('.wt_iew_overlay, .wt_iew_popup').hide();
				
					/* On Import failure */
					if (!wt_iew_import.import_finished) {

						var timeoutStatus = [504];
						if(!wt_iew_import.import_cancel_clicked) {
							jQuery('.wt-something-went-wrong').show();
							jQuery('.wt_iew_import_progress_wrap').show();
				
							if (-1 !== jQuery.inArray(jqXHR.status, timeoutStatus)) {
								jQuery('.wt-something-went-wrong div .wt-error-class .wt_iew_resume_import').show();
								jQuery('.wt-something-went-wrong div .wt-error-class .wt_iew_fatal_error').hide();
					
								// Add click event handler to the resume-import-button
								jQuery('.resume-import-button').on('click', function (event) {
									event.preventDefault();
									jQuery('.wt-something-went-wrong').hide();
									wt_iew_import.do_nonstep_action('import');
								});
							} else {

								jQuery('.wt-something-went-wrong div .wt-error-class .wt_iew_resume_import').hide();
								jQuery('.wt-something-went-wrong div .wt-error-class .wt_iew_fatal_error').show();
							}
						}
					}
				
				}
			});
		},
                show_import_popup:function()
		{
			var pop_elm=$('.wt_iew_import_progress_wrap');
			var ww=$(window).width();
			pop_w=(ww<1300 ? ww : 1300)-200;
			pop_w=(pop_w<200 ? 200 : pop_w);
			pop_elm.width(pop_w);

			wh=$(window).height();
			pop_h=(wh>=400 ? (wh-200) : wh);
			$('.wt_iew_import_progress').css({'max-height':pop_h+'px','overflow':'auto'});
			wt_iew_popup.showimportPopup(pop_elm);
                        pop_elm.css({'display':'flex'});
		},
                toHHMMSS:function(sec_taken){
                    var sec_num = parseInt(sec_taken, 10); // don't forget the second param
                    var hours   = Math.floor(sec_num / 3600);
                    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                    var seconds = sec_num - (hours * 3600) - (minutes * 60);
                    if(seconds === 0){
                        seconds = 1;
                    }

                    if (hours   < 10) {hours   = "0"+hours;}
                    if (minutes < 10) {minutes = "0"+minutes;}
                    if (seconds < 10) {seconds = "0"+seconds;}
                    return hours + ':' + minutes + ':' + seconds;
                },
		reg_button_actions:function()
		{
			$('.wt_iew_import_action_btn').unbind('click').click(function(e){
				e.preventDefault();

				wt_iew_import.remove_ajax_page_loader(); /* remove any loader that are open */

				var action=$(this).attr('data-action');
				var action_type=$(this).attr('data-action-type');
				var is_previous_step=wt_iew_import.is_previous_step(action);
				if(!wt_iew_importer_validate(action, action_type, is_previous_step))
				{
					return false; 
				}				

				/* validation section */
				if(!wt_iew_import.form_validation(action))
				{
					return false;
				}				

				/* if popover is opened */
				wt_iew_popover.closePop();

				/* this method will check current step is import method step and file is validated */
				if(!wt_iew_import.validate_file(action, action_type))
				{
					return false;	
				}

				if(action_type=='step')
				{
					wt_iew_import.change_step(action);
				}else
				{
					wt_iew_import.nonstep_actions(action);
				}	
			});
                        $('.wt_iew_popup_cancel_btn').unbind('click').click(function(e){
                            if (confirm(wt_iew_import_params.msgs.import_cancel_warn) == true) {
                                    var temp_err_message = wt_iew_params.msgs.error;
                                    wt_iew_params.msgs.error = wt_iew_import_params.msgs.import_canceled;
                                    wt_iew_import.import_finished = true;
                                    wt_iew_import.import_ajax_xhr.abort();
                                    wt_iew_params.msgs.error = temp_err_message;
                                    var progressval = 1;
                                    var elm = document.getElementsByClassName('progressab')[0];
                                    elm.style.width = progressval+"%";
                                    elm.innerText = progressval+"%";
                                    jQuery('.wt_iew_overlay, .wt_iew_popup').hide();
                                }
                        });
                        
		},
		get_file_from:function()
		{
			if(jQuery('select[name="wt_iew_file_from"]').length>0)  /* select box */
			{
				var file_from=jQuery('[name="wt_iew_file_from"]').val();
			}else
			{
				var file_from=jQuery('[name="wt_iew_file_from"]:checked').val();
			}
			return file_from;
		},
		set_validate_file_info:function()
		{
			var file_from=this.get_file_from();

			if(file_from=='local')
			{
				this.local_import_file=$('[name="wt_iew_local_file"]').val();

			}else if(file_from=='url')
			{
				this.url_import_file=$('[name="wt_iew_url_file"]').val();

			}else
			{                          
                                //wt_iew_set_validate_file_info(file_from);
                                setTimeout(function(){ wt_iew_set_validate_file_info(file_from); }, 1000);
			}
		},
		validate_file:function(action, action_type)
		{
			if(this.current_step=='method_import')
			{
				/* check any revalidation needed for input file */
				var file_from=this.get_file_from();
				if(file_from=='local')
				{
					if(this.local_import_file!=$('[name="wt_iew_local_file"]').val())
					{
						this.is_valid_file=false;
					}else
					{
						this.is_valid_file=true;
					}
				}else if(file_from=='url')
				{
					if(this.url_import_file!=$('[name="wt_iew_url_file"]').val())
					{
						this.is_valid_file=false;
					}else
					{
						this.is_valid_file=true;
					}
				}else
				{
					/* revalidation check of other remote adapters will done on form validation hook */
				}
			}

			if(this.current_step=='method_import') /*  && !this.is_valid_file  method import page, then check file validation is done. */
			{
				if(action_type=='step' && this.is_previous_step(action)) /* step action and previous step */
				{
					return true;
				}

				/* store the current action to a variable. After successful validation of the file the stopped action will resumed */
				this.blocked_action=action;

				this.set_import_progress_info(wt_iew_import_params.msgs.validating_file);
				this.nonstep_actions('validate_file'); /* download/upload the file and validate it. */
				return false;
			}else
			{
				return true;
			}
		},
		form_validation:function(step_to_go)
		{
			if(this.current_step=='post_type')
			{
				if(this.to_import=='')
				{
					wt_iew_notify_msg.error(wt_iew_import_params.msgs.select_post_type);
					return false;
				}
			}else if(this.current_step=='method_import') /* method import page */ 
			{
				if(this.import_method=='template' && this.selected_template==0 && !this.is_previous_step(step_to_go))
				{	
					wt_iew_notify_msg.error(wt_iew_import_params.msgs.select_an_import_template);
					return false;
				}
				if((this.import_method=='new' ||  this.import_method=='quick') && !this.is_previous_step(step_to_go))
				{
					if(this.file_from=='')
					{
						wt_iew_notify_msg.error(wt_iew_import_params.msgs.choose_import_from);
						return false;
					}else
					{
						if(this.file_from=='local' && $.trim($('[name="wt_iew_local_file"]').val())=='')
						{
							wt_iew_notify_msg.error(wt_iew_import_params.msgs.choose_a_file, false);
							return false;
						}
						else if(this.file_from=='url' && $.trim($('[name="wt_iew_url_file"]').val())=='')
						{
							wt_iew_notify_msg.error(wt_iew_import_params.msgs.choose_a_file, false);
							return false;
						}
					}
				}
			}else if(this.current_step=='advanced')
			{
				let batch_count = $('[name="wt_iew_batch_count"]').val();
				if(batch_count > 100 ){
					wt_iew_notify_msg.error(wt_iew_import_params.msgs.batch_count_limit, false);
					return false;
				}
			}
			return true;
		},
		change_step:function(step_to_go)
		{
			/* setting fromdata */
			this.prepare_form_data();

			/* step changing section */
			this.show_step_page(step_to_go, true);
		},
		reset_form_data:function()
		{
			this.form_data={};
			this.selected_template=0;
			this.selected_template_name='';
			this.import_method='';
			this.is_valid_file=false;
			this.local_import_file='';
			this.url_import_file='';
			wt_iew_importer_reset_form_data();

			/* reset loaded state */
			this.reset_step_loaded_state(this.get_need_to_reload_steps());
		},
		reset_step_loaded_state:function(steps)
		{
			this.set_step_loading_status(steps, 'not_loaded');
		},
		reset_meta_step_loaded_state:function()
		{
			var rest_steps=this.get_need_to_reload_steps();
			var meta_step=rest_steps.shift();
			this.reset_step_loaded_state([meta_step]);
		},
		prepare_form_data:function()
		{
			if(this.current_step=='post_type')
			{	
				this.form_data['post_type_form_data']=JSON.stringify({'item_type':wt_iew_import.to_import});
			}
			else if(this.current_step=='mapping')
			{
				
				/**
				* Default mapping fields 
				*/
				var mapping_form_data={};
				var mapping_fields={};
				var mapping_selected_fields={}; /* this value is only for backend processing */

				$('.wt-iew-importer-default-mapping-tb tbody tr').each(function(){
					
					var columns_key=$(this).find('.columns_key').val();
					var columns_val=$(this).find('.columns_val').val();
					
					var enabled=($(this).find('.columns_key').is(':checked') ? 1 : 0);
					var type=$(this).find('.columns_val').attr('data-type');
					mapping_fields[columns_key]=[columns_val, enabled, type];
					if(enabled==1)
					{
						mapping_selected_fields[columns_key]=columns_val;
					}

				});

				mapping_form_data={'mapping_fields':mapping_fields, 'mapping_selected_fields':mapping_selected_fields};
				if(this.form_data === undefined){
                                    this.form_data={};
                                    this.form_data['post_type_form_data']=JSON.stringify({'item_type':wt_iew_import.to_import});
                                        var current_form_data={};
					current_form_data={'method_import' : wt_iew_import.import_method, 'selected_template':this.selected_template};
				
                                }
                                this.form_data['mapping_form_data']=JSON.stringify(mapping_form_data);
				
				
				/**
				* meta mapping fields 
				*/
				var meta_step_form_data={};
				var mapping_fields={};
				var mapping_selected_fields={}; /* this value is only for backend processing */

				$('.wt-iew-importer-meta-mapping-tb').each(function(){
					var mapping_key=$(this).attr('data-field-type');
					mapping_fields[mapping_key]={};
					mapping_selected_fields[mapping_key]={};
					
					$(this).find('tbody tr').each(function(){						
						if($(this).find('.columns_key').length>0 && $(this).find('.columns_val').length>0)
						{
							var columns_key=$(this).find('.columns_key').val();
							var columns_val=$(this).find('.columns_val').val();
							
							var enabled=($(this).find('.columns_key').is(':checked') ? 1 : 0);
							var type=$(this).find('.columns_val').attr('data-type');
							mapping_fields[mapping_key][columns_key]=[columns_val, enabled, type];
							if(enabled==1)
							{
								mapping_selected_fields[mapping_key][columns_key]=columns_val;
							}							
						}
					});
				});

				meta_step_form_data={'mapping_fields':mapping_fields, 'mapping_selected_fields':mapping_selected_fields};
				this.form_data['meta_step_form_data']=JSON.stringify(meta_step_form_data);

			}
			else
			{
				var current_form_data={};
				if(this.current_step=='method_import')
				{
					current_form_data={'method_import' : wt_iew_import.import_method, 'selected_template':this.selected_template};
				}
				if($('.wt_iew_import_'+this.current_step+'_form').length>0) /* may be user hit the back button */
				{
					var form_data=$('.wt_iew_import_'+this.current_step+'_form').serializeArray();
					$.each(form_data, function(){
						
						if(current_form_data[this.name])
						{
							if(!current_form_data[this.name].push)
							{
								current_form_data[this.name] = [current_form_data[this.name]];
							}
							current_form_data[this.name].push(this.value || '');
						}else
						{
							current_form_data[this.name] = this.value || '';
						}

					});

					this.form_data[this.current_step+'_form_data']=JSON.stringify(current_form_data);
				}
			}

			/* we are resetting formdata on second step. If user not going to first step then post type formdata will be empty. */
			if(this.current_step!='post_type')
			{	
				this.form_data['post_type_form_data']=JSON.stringify({'item_type':wt_iew_import.to_import});
			}


		},
		is_multi_select:function(name)
		{
			var elm=$('[name="'+name+'"]');
			if(elm.prop("tagName").toLowerCase()=='select' && this.has_attr(elm,'multiple'))
			{
				return true;
			}else
			{
				return false;
			}
		},
		has_attr:function(elm,attr_name)
		{
			var attr = elm.attr(attr_name);
			if(typeof attr!==typeof undefined  &&  attr!==false)
			{
				return true;
			}else
			{
				return false;
			}
		},
		is_step:function(step_key)
		{
			return wt_iew_import_params.steps.hasOwnProperty(step_key) ? true : false;
		},
		is_previous_step:function(step_key)
		{
			if(this.is_step(step_key)) 
			{				
				if(this.step_keys.indexOf(step_key)<this.step_keys.indexOf(this.current_step))
				{
					return true;
				}
			}
			return false;
		},
		is_step_loaded:function(step)
		{
			if(this.get_page_dom_object(step).length==0){ return true; } /* block infinite loop, if element is not available */ 
			return (this.get_page_dom_object(step).attr('data-loaded')==this.loaded_status_arr['loaded']);
		},
		set_step_loading_status:function(steps, status)
		{
			$.each(steps, function(step_ind, step){
			  	wt_iew_import.get_page_dom_object(step).attr('data-loaded', wt_iew_import.loaded_status_arr[status]);
			});
		},
		show_step_page:function(step, force_check_loaded)
		{
			$('.wt_iew_import_step').hide();	
			this.get_page_dom_object(step).show();
                        if(step == 'advanced'){
                            jQuery('.media-button.wt_iew_import_action_btn').prop('disabled', false);
                        }
			this.current_step=step;
			$(window).scrollTop(0);
			if(force_check_loaded)
			{	
				if(this.is_step_loaded(step))
				{
					this.current_step_actions();
				}else
				{
					this.refresh_step(true);
				}
			}else
			{
				this.current_step_actions();
			}
			wt_iew_form_toggler.runToggler();
		},
		current_step_actions:function() /* current page actions after page is visible */
		{	
			if(this.current_step=='method_import')
			{
				wt_iew_file_attacher.Set();
				wt_iew_form_toggler.runToggler();
                                
                                if(this.import_method == 'template'){
                                    if( this.selected_template == 0){
                                        $('.wt-iew-import-method-options-template').not('.wt-iew-import-template-sele-tr').hide();
                                    }else{
                                        $('.wt-iew-import-method-options-template').show();
                                    }
                                        
                                }
			}
			else if(this.current_step=='advanced')
			{                                
				wt_iew_form_toggler.runToggler();
                                wt_field_group.Set();
			}
			else if(this.current_step=='mapping')
			{
				wt_iew_popover.Set();

				if($('.meta_mapping_box_con').length>0)
				{
					if($('.meta_mapping_box_con[data-loaded="0"]').length>0)
					{
						this.load_meta_mapping_fields();
					}
				}
			}		
		},
		show_post_type_name:function()
		{
			if(this.to_import!="" && this.to_import_title=='')
			{
				$('.wt_iew_post-type-card').val(this.to_import);
				this.to_import_title = $('.wt_iew_post-type-card.selected').find('h3').text();
			}
			$('.wt_iew_step_head_post_type_name').html(this.to_import_title);
			if(this.to_import_title.includes('User'))
			$('#user-required-field-message').show();
		    	if(this.to_import_title.includes('Subscription'))
			$('#subscription-required-field-message').show();

                        if(wt_iew_params.pro_plugins[this.to_import]){
                            $('#sample-csv-file').attr("href", wt_iew_params.pro_plugins[this.to_import]['sample_csv_url']); 
                        }
			
		},
		page_actions:function(step)
		{
            jQuery(".wt-iew-tips").tipTip({'attribute': 'data-wt-iew-tip', 'delay': 0, 'edgeOffset':8});
			if(step=='post_type') /* post type page */
			{
				$(document).off('click', '.wt_iew_post-type-card').on( 'click', '.wt_iew_post-type-card', function() {					
					wt_iew_import.reset_form_data();
					var selectedPostTypes = wt_iew_params.pro_plugins;
					var postTypesKeys = Object.keys(selectedPostTypes);
						
					// Iterate over the keys if needed
					$.each(postTypesKeys, function(index, key) {
						$('.wt_iew_image_' + key).show();
						$('.wt_iew_active_image_' + key).hide();
					});
					
					var selectedPostType = $(this).data('post-type');
					wt_iew_import.to_import = selectedPostType;
					wt_iew_import.to_import_title = '';
					
					$('.wt_iew_post_type_name').html('');
					
					var postTypeName = $(this).find('h3').text();
					wt_iew_import.to_import_title = postTypeName;

					wt_iew_import.set_step_loading_status([wt_iew_import.step_keys[1]], 'not_loaded'); // resetting status for force refresh
					wt_iew_import.load_steps([wt_iew_import.step_keys[1]]);
					
					// Remove the 'selected' class from all cards
					$('.wt_iew_post-type-card').removeClass('selected');
					// Add the 'selected' class to the clicked card
					$(this).addClass('selected');
					
					$('.wt_iew_image_' + selectedPostType).hide();
					$('.wt_iew_active_image_' + selectedPostType).show();
				});
			}
			else if(step=='method_import') /* method import page */ 
			{
				this.import_method=$('[name="wt_iew_import_method_import"]:checked').val();
				this.toggle_import_method_options();

				$('[name="wt_iew_import_method_import"]').unbind('click').click(function(){
					var vl=$(this).val();
					if(wt_iew_import.import_method==vl)
					{
						return false;
					}
					wt_iew_import.reset_form_data();
					wt_iew_import.import_method=vl;
					wt_iew_import.refresh_step();					
				});

				$('.wt-iew-import-template-sele').unbind('change').change(function(){                                                                                
					wt_iew_import.selected_template=$(this).val();
					wt_iew_import.is_valid_file=false;				
					if(wt_iew_import.selected_template==0)
					{
						wt_iew_notify_msg.error(wt_iew_import_params.msgs.select_an_import_template);
					}else
					{
						/* reset step loaded sataus */
						wt_iew_import.reset_step_loaded_state(wt_iew_import.get_need_to_reload_steps());

						wt_iew_import.selected_template_name=$.trim($('.wt-iew-import-template-sele option:selected').text());
						wt_iew_import.refresh_step();
					}
				});


				/* callback for external adapters */
				if($('select[name="wt_iew_file_from"]').length>0) /* multiple adapter exists so select box */
				{
					this.file_from=$('[name="wt_iew_file_from"]').val();
					$('[name="wt_iew_file_from"]').unbind('change').on('change',function(){
						wt_iew_import.file_from=$(this).val();
						wt_iew_import.is_valid_file=false;
						wt_iew_set_file_from_fields(wt_iew_import.file_from);
					});
				}else  /* radio button */
				{
					this.file_from=$('[name="wt_iew_file_from"]:checked').val();
					$('[name="wt_iew_file_from"]').on('click',function(){
						wt_iew_import.file_from=$('[name="wt_iew_file_from"]:checked').val();
						wt_iew_import.is_valid_file=false;
						wt_iew_set_file_from_fields(wt_iew_import.file_from);
					});
				}
				wt_iew_set_file_from_fields(wt_iew_import.file_from);

				/* CSV delimiter form toggler. Custom and preset delimiter */
				wt_iew_custom_and_preset.delimiter_toggler();
                                                                                                
				/* Input date format form toggler. Custom and preset date format */
				wt_iew_custom_and_preset.date_format_toggler();

				wt_iew_dropzone.init('wt_iew_local_file_dropzone');
                                /* Auto populate template file. */
				wt_iew_dropzone.auto_populate();
                                wt_iew_import.warn_on_refresh(); 
			}
			else if(step=='mapping') /* mapping page */ 
			{
				this.enable_sortable();
				this.mapping_box_accordian();
				this.reg_mapping_field_bulk_action();
				wt_iew_popover.Set();
                                wt_iew_import.warn_on_refresh(); 
			}
			else if(step=='advanced')
			{	
                            window.scrollTo(0,0);
                            wt_iew_import.warn_on_refresh(); 
			}

			/* common events */
			if($('.wt_iew_datepicker').length>0)
			{
				$('.wt_iew_datepicker').datepicker();
			}
			wt_field_group.Set();
			wt_iew_form_toggler.Set();
			wt_iew_conditional_help_text.Set(this.get_page_dom_object(step));
		},
		enable_sortable:function()
		{
			$('.meta_mapping_box_con[data-sortable="0"]').each(function(){
				var tb=$(this).find(".wt-iew-mapping-tb tbody");
				if(tb.length>0 && typeof tb.sortable === 'function')
				{
					tb.sortable({
						handle: ".wt_iew_sort_handle",
						placeholder: "wt-iew-sortable-placeholder",
						forcePlaceholderSize: true,
						revert:true
					});
					$(this).attr('data-sortable', 1);
				}
			});			
		},
		toggle_import_method_options:function()
		{
			$('.wt-iew-import-method-options').hide();
			$('.wt-iew-import-method-options-'+this.import_method).show();
		},
		mapping_box_accordian:function()
		{
			$('.meta_mapping_box_hd').unbind('click').click(function()
			{
				/* if popover is opened */
				wt_iew_popover.closePop();

				var c_dv=$(this).parents('.meta_mapping_box').find('.meta_mapping_box_con');
				if(c_dv.is(':visible'))
				{
					c_dv.hide();
					$(this).find('.dashicons').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-right');
				}else
				{
					c_dv.show();
					c_dv.find(".wt-iew-mapping-tb tbody tr td").each(function(){
						$(this).css({'width':$(this).width()});
					});
					$(this).find('.dashicons').removeClass('dashicons-arrow-right').addClass('dashicons-arrow-down');
				}
			});
		},
		mapping_fields_selected_count:function(tb_elm)
		{
			tb_elm.parents('.meta_mapping_box').find('.meta_mapping_box_selected_count_box_num').text(tb_elm.find('.wt_iew_mapping_checkbox_sub:checked').length);
		},
		reg_mapping_field_bulk_action:function()
		{
			$('.wt_iew_mapping_checkbox_main').each(function()
			{
				var tb=$(this).parents('.wt-iew-mapping-tb');
				if(tb.find('.wt_iew_mapping_checkbox_sub:checked').length==tb.find('.wt_iew_mapping_checkbox_sub').length)
				{
					tb.find('.wt_iew_mapping_checkbox_main').prop('checked',true);
				}else
				{
					tb.find('.wt_iew_mapping_checkbox_main').prop('checked',false);
				}
				wt_iew_import.mapping_fields_selected_count(tb);
			});

			$('.wt_iew_mapping_checkbox_main').unbind('click').click(function()
			{
				var tb=$(this).parents('.wt-iew-mapping-tb');
				if($(this).is(':checked'))
				{
					tb.find('.wt_iew_mapping_checkbox_sub').prop('checked',true);
				}else
				{
					tb.find('.wt_iew_mapping_checkbox_sub').prop('checked',false);
				}
				wt_iew_import.mapping_fields_selected_count(tb);
			});
			$('.wt_iew_mapping_checkbox_sub').unbind('click').click(function()
			{
				var tb=$(this).parents('.wt-iew-mapping-tb');
				if($(this).is(':checked') && tb.find('.wt_iew_mapping_checkbox_sub:checked').length==tb.find('.wt_iew_mapping_checkbox_sub').length)
				{
					tb.find('.wt_iew_mapping_checkbox_main').prop('checked',true);
				}else
				{
					tb.find('.wt_iew_mapping_checkbox_main').prop('checked',false);
				}
				wt_iew_import.mapping_fields_selected_count(tb);
			});
		},
		set_step_page:function(data)
		{
			page_html=data.page_html;
			$.each(page_html, function(step_id, page_content){
			  	wt_iew_import.get_page_dom_object(step_id).html(page_content);
			  	wt_iew_import.page_actions(step_id);
			  	
			  	if(step_id=='method_import' && (wt_iew_import.selected_template>0 || wt_iew_import.on_rerun))
			  	{
			  		wt_iew_import.form_data=data.template_data;

			  		if(wt_iew_import.on_rerun)
			  		{
			  			if($('.wt-iew-import-template-sele').val()==0)
						{
							wt_iew_import.selected_template=0;
							wt_iew_import.selected_template_name='';
						}else
						{
							wt_iew_import.selected_template=$('.wt-iew-import-template-sele').val();
							wt_iew_import.selected_template_name=$.trim($('.wt-iew-import-template-sele option:selected').text());
						}
			  		}
			  	}
			  	wt_iew_import.show_post_type_name();
			});
			this.reg_button_actions();
		},
		prepare_ajax_data:function(action, data_type)
		{

			    this.ajax_data = {
	            '_wpnonce': wt_iew_params.nonces.main,
	            'action': "iew_import_ajax",
	            'import_action': action,
	            'selected_template': this.selected_template,
	            'to_import': this.to_import,
	            'data_type': data_type,
	            'import_method': this.import_method,
	            'temp_import_file': this.temp_import_file,
	        };
                
                    if($('[name="wt_iew_delimiter"]').length>0){
                        this.import_delimiter = $('[name="wt_iew_delimiter"]').val();
                        this.ajax_data['delimiter'] = this.import_delimiter;
                    }
                
		},
		mapping_field_editor:function()
		{
			mapping_field_editor.Set();
		},
		mapping_field_editor_validate_column_val:function(vl)
		{
			return mapping_field_editor.validate_column_val(vl);
		},
		mapping_field_editor_output_preview:function()
		{
			mapping_field_editor.output_preview();
		},
                mapping_field_editor_output_preview_el:function(el, str)
		{
			mapping_field_editor.output_preview_el(el, str);
		},
		is_object:function(obj)
		{
		    return obj !== undefined && obj !== null && obj.constructor == Object;
		},
                warn_on_refresh: function () {
                    window.onbeforeunload = function (event)
                    {
                        if(!wt_iew_import.import_finished){
                            return confirm("Changes that you made may not be saved.");
                        }
                    };
                }
	}

	var mapping_field_editor=
	{
		text_area_pos:null,
		popover:null,
		Set:function()
		{
			this.popover=$('.wt_iew_popover-content');
			this.add_fields();

			this.popover.find('.wt_iew_mapping_field_editor_expression').unbind('keyup').on('keyup', function(){
				mapping_field_editor.text_area_pos=$(this).getCursorPosition();
				mapping_field_editor.output_preview();
			});

			this.search_column();
		},
		validate_columns:function()
		{
			$('.meta_mapping_box_con[data-field-validated="0"]').each(function(){
				var tb=$(this).find('.wt-iew-mapping-tb');
				if($(this).find('.wt-iew-mapping-tb').length>0)
				{
					$(this).attr({'data-field-validated':1});
					tb.find('.columns_val').each(function(){
						var vl=$.trim($(this).val());
						if(vl!="")
						{
							var html_vl=mapping_field_editor.validate_column_val(vl);
							$(this).siblings('[data-wt_iew_popover="1"]').html(html_vl);
						}
					});
				}
			});	
		},
		validate_column_val:function(str)
		{
			const regex = /\{([^}]+)\}/g;
			let m;
			var out=str;
			while ((m = regex.exec(str)) !== null) 
			{
			    /* This is necessary to avoid infinite loops with zero-width matches */
			    if (m.index === regex.lastIndex) {
			        regex.lastIndex++;
			    }		    
			    /* The result can be accessed through the `m`-variable. */
			    m.forEach((match, groupIndex) => { 
			    	
			    	/* check date format matching */
			    	var match_arr=match.split(wt_iew_import_params.wt_iew_prefix+'@');
			    	if(match_arr.length==2)/* date format matched */
					{
						match=match_arr[0];
					}

			    	if(!wt_iew_file_head_default.hasOwnProperty(match) && !wt_iew_file_head_meta.hasOwnProperty(match))
			    	{ 
			    		out=out.replace('{'+match+'}', '<span class="wt_iew_invalid_mapping_field">{'+match+'}</span>');	
			    	}
			    });
			}
			return out;
		},
		add_fields:function()
		{			
			this.popover.find('.wt_iew_mapping_field_selector li').unbind('click').click(function(){
				var vl=' {'+$(this).attr('data-val')+'} ';
				var exp_vl=mapping_field_editor.popover.find('.wt_iew_mapping_field_editor_expression').val();
				if(mapping_field_editor.text_area_pos!==null)
				{
					var new_vl=exp_vl.substr(0, mapping_field_editor.text_area_pos)+vl+exp_vl.substr(mapping_field_editor.text_area_pos);
				}else
				{
					var new_vl=exp_vl+vl;
				}
				mapping_field_editor.popover.find('.wt_iew_mapping_field_editor_expression').val(new_vl);
				mapping_field_editor.output_preview();
			})
		},
		search_column:function()
		{
			/* my template search */
			this.popover.find('.wt_iew_mapping_field_editor_column_search').unbind('keyup').on('keyup',function(){
				var vl=$.trim($(this).val());
				if(vl!="")
				{
					vl=vl.toLowerCase();
					mapping_field_editor.popover.find('.wt_iew_mapping_field_selector li').hide();
					var kk=mapping_field_editor.popover.find('.wt_iew_mapping_field_selector li').filter(function(){
						var name=$(this).attr('data-val');
						name=name.toLowerCase();
						if(name.search(vl)!=-1)
						{
							return true;
						}else
						{
							return false;
						}
					});
					kk.show();
					if(mapping_field_editor.popover.find('.wt_iew_mapping_field_selector li:visible').length==0)
					{
						mapping_field_editor.popover.find('.wt_iew_mapping_field_selector_no_column').show();
					}else
					{
						mapping_field_editor.popover.find('.wt_iew_mapping_field_selector_no_column').hide();
					}
				}else
				{
					mapping_field_editor.popover.find('.wt_iew_mapping_field_selector li').show();
				}
			});
			mapping_field_editor.popover.find('.wt_iew_mapping_field_selector_no_column').hide();
		},
		output_preview:function()
		{
			this.popover.find('.wt_iew_mapping_field_editor_er').html('');
			const str = this.popover.find('.wt_iew_mapping_field_editor_expression').val();
			var out='';
			out=this.add_sample_data(str,true); 
			out=this.do_arithmetic(out);
                        out=this.add_sample_data(out,false);
			this.popover.find('.wt_iew_mapping_field_editor_sample').html(out);
		},
		output_preview_el:function(el,str)
		{
			var out='';
			out=this.add_sample_data(str,true); 
			out=this.do_arithmetic(out);
                        out=this.add_sample_data(out,false);
			el.html(out);
                        el.parents('td').css({'background':'#f6f6f6'});
		},                
		add_sample_data:function(str,skip_from_evaluation)
		{
			const regex = /\{([^}]+)\}/g;
			
			let m;
			var out=str;

                        if(mapping_field_editor.IsJsonString(out)){
                                    return out;
                                    
                        }

                        if(mapping_field_editor.is_serialized(str)){
                                    return str;
                                    
                        }

			while ((m = regex.exec(str)) !== null) {
			    /* This is necessary to avoid infinite loops with zero-width matches */
			    if (m.index === regex.lastIndex) {
			        regex.lastIndex++;
			    }		    
			    /* The result can be accessed through the `m`-variable. */
			    m.forEach((match, groupIndex) => {
			    	
			    	/* check date format matching */
			    	var match_arr=match.split(wt_iew_import_params.wt_iew_prefix+'@');
			    	if(match_arr.length==2)/* date format matched */
					{
						match=match_arr[0];
					}

			    	var sample_vl=' '; /* do not set default value as empty string */
			    	if(wt_iew_file_head_default.hasOwnProperty(match))
			    	{ 
			    		var sample_vl=$.trim(wt_iew_file_head_default[match]);
			    	}
			    	else if(wt_iew_file_head_meta.hasOwnProperty(match))
			    	{
			    		var sample_vl=$.trim(wt_iew_file_head_meta[match]);
			    	}

			    	if(match_arr.length==2)/* date format matched */
			    	{
			    		match=match_arr.join(wt_iew_import_params.wt_iew_prefix+'@');
			    		if(sample_vl!="")
			    		{
			    			sample_vl=mapping_field_editor.format_date(sample_vl);
			    		}
			    	}

			    	sample_vl=(sample_vl!="" ? sample_vl : '<span class="wt_iew_no_sample_mapping_data">'+match+'</span>');
                                                                
                                if(match.indexOf("line_item_") != -1 && skip_from_evaluation){                                    
                                    match =  match.slice(0,-((/[^_]*$/.exec(match)[0]).length));
                                }
                                
                                
                                
                                if(jQuery.inArray(match,wt_iew_skip_from_evaluation_array) != -1 && skip_from_evaluation){ /*  */
                                    out=out;
                                }else{
                                    out=out.replace('{'+match+'}', sample_vl);
                                }

			    });
			}
			return out;
		},
                IsJsonString: function (str) {
                    try {
                        JSON.parse(str);
                    } catch (e) {
                        return false;
                    }
                    return true;
                },
                is_serialized: function (str)
                {
                    if (typeof str !== 'string')
                    {
                        return false;
                    }                    
                    if (str === 'b:0;')
                    {
                        return true;
                    }

                    var length = str.length;
                    var end = '';
                    switch (str.charAt(0))
                    {
                        case 's':
                            if (str.charAt(length - 2) !== '"')
                            {
                                return false;
                            }
                        case 'b':
                        case 'i':
                        case 'd':
                            end += ';';
                        case 'a':
                        case 'O':
                            end += '}';
                            if (str.charAt(1) !== ':')
                            {
                                return false;
                            }
                            switch (str.charAt(2))
                            {
                                case '0':
                                case '1':
                                case '2':
                                case '3':
                                case '4':
                                case '5':
                                case '6':
                                case '7':
                                case '8':
                                case '9':
                                    break;

                                default:
                                    return false;
                            }
                        case 'N':
                            end += ';';
                            if (str.charAt(length - 1) !== end.charAt(0))
                            {
                                return false;
                            }
                            break;

                        default:
                            return false;
                    }

                    return true;
                },
		add_zero:function(i)
		{
			if(i<10)
			{
				i="0"+i;
			}
			return i;
		},
		format_date:function(date_string)
		{
			var d = new Date(date_string);
			if(d instanceof Date && !isNaN(d))
			{
				date_string=d.getFullYear()+'-'+this.add_zero(d.getMonth()+1)+'-'+this.add_zero(d.getDate())+' '+this.add_zero(d.getHours())+':'+this.add_zero(d.getMinutes())+':'+this.add_zero(d.getSeconds());
            }
            return date_string;
		},
		do_arithmetic:function(str)
		{
			const regex_arith = /\[([0-9()+\-*/. ]+)\]/g;
			let m;
			var out=str;
			while ((m = regex_arith.exec(str)) !== null) {
			    /* This is necessary to avoid infinite loops with zero-width matches */
			    if (m.index === regex_arith.lastIndex) {
			        regex_arith.lastIndex++;
			    }
			    try{
			    	eqn='('+m[1]+')';
			        eval("var eqn_eval = " + eqn.toLowerCase());
			        out=out.replace(m[0], eqn_eval);
			    } catch(e) {
			        mapping_field_editor.popover.find('.wt_iew_mapping_field_editor_er').html(e);
			        return false;
			    }
			}
			return out;
		}
	}

	return wt_iew_import;	
})( jQuery );

(function ($, undefined) {
    $.fn.getCursorPosition = function() {
        var el = $(this).get(0);
        var pos = 0;
        if('selectionStart' in el) {
            pos = el.selectionStart;
        } else if('selection' in document) {
            el.focus();
            var Sel = document.selection.createRange();
            var SelLength = document.selection.createRange().text.length;
            Sel.moveStart('character', -el.value.length);
            pos = Sel.text.length - SelLength;
        }
        return pos;
    }
})(jQuery);

/**
 *	Dropzone initaiting section
 * 	More info at [www.dropzonejs.com](http://www.dropzonejs.com)
 */
var wt_iew_dropzone=
{
	elm:null,
	old_file:false,
	Set:function()
	{
		if(typeof Dropzone==='undefined'){
			return false;
		}
		Dropzone.autoDiscover = false;
                this.auto_populate();
	},
        auto_populate:function()
	{
		var template_val=jQuery.trim(jQuery('#local_file').val());
		if(template_val!="")
		{
			var file_name=template_val.split('/').pop();
			this.set_success(file_name);
		}		
	},
	set_success:function(file_name)
	{
		jQuery(".wt_iew_dz_file_success").html(wt_iew_import_params.msgs.upload_done);
                jQuery(".wt_iew_dz_remove_link").html(wt_iew_import_params.msgs.remove);
                jQuery(".wt_iew_dz_file_name").html(file_name);
                jQuery(".dz-message").css({'margin-top':'60px'});
                
                /* register file deleting event */
	    	wt_iew_dropzone.remove_file();
	},
	init:function(elm_id)
	{
		if(typeof Dropzone==='undefined'){
			return false;
		}
		this.elm=jQuery("#"+elm_id);

		var ajax_data={
	            '_wpnonce': wt_iew_params.nonces.main,
	            'action': "iew_import_ajax",
	            'import_action': 'upload_import_file',
	            'data_type': 'json',
	            'file_url': '',
	       	};
		var drop_zone_obj = new Dropzone(
			"#"+elm_id, { 
				url:wt_iew_params.ajax_url,
				createImageThumbnails:false,
				acceptedFiles:wt_iew_import_params.allowed_import_file_type_mime.join(", "),
				paramName:'wt_iew_import_file',
				dictDefaultMessage:wt_iew_import_params.msgs.drop_upload,
				dictInvalidFileType:wt_iew_import_params.msgs.invalid_file,
				dictResponseError:wt_iew_import_params.msgs.server_error,
				params:ajax_data,
				uploadMultiple:false,
				parallelUploads:1,
				maxFiles:1,
				timeout:0,
				maxFilesize:wt_iew_import_params.max_import_file_size,
				previewTemplate:"<div class=\"dz-preview dz-file-preview\">\n <div class=\"dz-upload-info\"></div> \n <div class=\"dz-details\">\n  <div class=\"dz-filename\"><span data-dz-name></span></div>\n </div>\n  <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>\n </div>",
			}
		);

		drop_zone_obj.on("addedfile", function(file) {
	    	jQuery(".dz-upload-info").html(wt_iew_import_params.msgs.uploading);
	    	jQuery(".dz-message").css({'margin-top':'85px'});

	    	var dropzone_target=wt_iew_dropzone.elm.attr('wt_iew_dropzone_target');
			var dropzone_target_elm=jQuery(dropzone_target);
			if(dropzone_target_elm.length>0)
			{
				var file_url=dropzone_target_elm.val();
				if(file_url!="")
				{
					drop_zone_obj.options.params['file_url']=file_url; /* this is to remove the already uploaded file */
				}
			}

	  	});
	  
	  	drop_zone_obj.on("dragstart", function(file) {
	    	wt_iew_dropzone.elm.addClass('wt_drag_start');
	  	});

	  	drop_zone_obj.on("dragover", function(file) {
	    	wt_iew_dropzone.elm.addClass('wt_drag_start');
	  	});

	  	drop_zone_obj.on("dragleave", function(file) {
	    	wt_iew_dropzone.elm.removeClass('wt_drag_start');
	  	});

	  	drop_zone_obj.on("drop", function(file) {
	    	wt_iew_dropzone.elm.removeClass('wt_drag_start');
	  	});

	  	drop_zone_obj.on("dragend", function(file) {
	    	wt_iew_dropzone.elm.removeClass('wt_drag_start');
	  	});

	  	drop_zone_obj.on("fallback", function(file) {
	    	wt_iew_dropzone.elm.html(wt_iew_import_params.msgs.outdated);
			return null;
	  	});
	  	drop_zone_obj.on("error", function(file, message) {
	    	drop_zone_obj.removeFile(file);
	    	wt_iew_notify_msg.error(message);
	  	});

	  	drop_zone_obj.on("success", function(file, response) {
	    
	    	var file_name=file.name;

	    	/* remove file obj */
	    	drop_zone_obj.removeFile(file);
	    	

	    	if(wt_iew_dropzone.isJson(response))
	    	{
	    		response=JSON.parse(response);
	    		if(response.status==1)
	    		{
	    			wt_iew_dropzone.set_success(file_name);
	    			
	    			var dropzone_target=wt_iew_dropzone.elm.attr('wt_iew_dropzone_target');
	    			var dropzone_target_elm=jQuery(dropzone_target);
	    			if(dropzone_target_elm.length>0)
	    			{
	    				dropzone_target_elm.val(response.url);

	    			}
	    		}else
	    		{
	    			wt_iew_notify_msg.error(response.msg);
	    		}
	    	}else
	    	{
	    		wt_iew_notify_msg.error(wt_iew_params.msgs.error);
	    	}
	  	});
	},
	remove_file:function()
	{
		jQuery('.wt_iew_dz_remove_link').unbind('click').click(function(e){
			e.stopPropagation();

			var dropzone_target=wt_iew_dropzone.elm.attr('wt_iew_dropzone_target');
			var dropzone_target_elm=jQuery(dropzone_target);
			if(dropzone_target_elm.length>0)
			{
				var file_url=dropzone_target_elm.val();
				if(file_url!="")
				{
					dropzone_target_elm.val('');
					jQuery(".wt_iew_dz_file_success, .wt_iew_dz_remove_link, .wt_iew_dz_file_name").html('');
					jQuery(".dz-message").css({'margin-top':'85px'});

					jQuery.ajax({
						type: 'POST',
		            	url:wt_iew_params.ajax_url,
		            	data:{ 
		            		'_wpnonce': wt_iew_params.nonces.main,
				            'action': "iew_import_ajax",
				            'import_action': 'delete_import_file',
				            'data_type': 'json',
				            'file_url':file_url,
				        },
		            	dataType:'json'

					});
				}
			}
		});
	},
	isJson:function(str)
	{
	    try {
	        JSON.parse(str);
	    } catch (e) {
	        return false;
	    }
	    return true;
	}
}
wt_iew_dropzone.Set();


jQuery(function() {		

	if(wt_iew_import_params.rerun_id>0)
	{
		wt_iew_import.to_import=wt_iew_import_params.to_import;
		wt_iew_import.import_method=wt_iew_import_params.import_method;
		wt_iew_import.rerun_id=wt_iew_import_params.rerun_id;
                wt_iew_import.cron_edit_id=wt_iew_import_params.cron_edit_id;
		wt_iew_import.on_rerun=true;
		wt_iew_import.is_valid_file=true;
		wt_iew_import.temp_import_file=wt_iew_import_params.temp_import_file;                
                setTimeout(function(){ 
                    wt_iew_import.show_step_page('mapping', true); 
                    if(jQuery('.wt_iew_import_action_btn').attr('data-action') == 'method_import'){
                        jQuery('.media-button.wt_iew_import_action_btn').prop('disabled', true);
                    }
                }, 1000);
                
	}
	wt_iew_import.Set();	
});
