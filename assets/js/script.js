jQuery(document).ready(function ($) {

	"use strict"

	Merlin.callbacks['verify_api_key'] = function (btn) {
		new CheckAPIKey().init(btn);
	};
	Merlin.callbacks['import_lists'] = function (btn) {
		new ImportLists(btn);
	};

	if ($('.merlin__content--selectlists').length) {

		var wrap = $('.mailchimp-lists'),
			tmpl = wrap.find('li');

		_ajax('lists', function (response) {
			$.each(response.data.lists, function (i, list) {
				var clone = tmpl.clone().removeClass('hidden').addClass('lead').data('id', list.id);
				clone.find('span').html(list.name + ' (' + list.stats.member_count + ')');
				clone.find('label').attr('for', 'list-' + list.id);
				clone.find('input').attr('id', 'list-' + list.id).prop('checked', list.stats.member_count);
				setTimeout(function () {
					clone.hide().appendTo(wrap).slideDown();
				}, 10 * i);
			});
		});
	}

	function _ajax(action, data, callback, errorCallback) {

		if ($.isFunction(data)) {
			if ($.isFunction(callback)) {
				errorCallback = callback;
			}
			callback = data;
			data = {};
		}
		$.ajax({
			type: 'POST',
			url: merlin_params.ajaxurl,
			data: $.extend({
				action: 'mailster_mailchimp_' + action,
				_wpnonce: merlin_params.wpnonce
			}, data),
			success: function (data, textStatus, jqXHR) {
				callback && callback.call(this, data, textStatus, jqXHR);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				if (textStatus == 'error' && !errorThrown) return;
				if (console) console.error($.trim(jqXHR.responseText));
				errorCallback && errorCallback.call(this, jqXHR, textStatus, errorThrown);
			},
			dataType: "JSON"
		});
	}


	function CheckAPIKey() {
		var body = $('.merlin__body');
		var complete, $button, notice = $("#apikey-info-text");

		function on_error(r) {

			$button.removeClass("merlin__button--loading").data("done-loading", "no");

			notice.addClass("error");
			notice.html(r.responseJSON.data.error);
		}

		function do_ajax() {
			jQuery.post(merlin_params.ajaxurl, {
				action: "mailster_mailchimp_verify_api_key",
				apikey: $('#api-key').val(),
				wpnonce: merlin_params.wpnonce,
			}, complete).fail(on_error);
		}

		return {
			init: function (btn) {
				$button = $(btn);
				complete = function (r) {

					setTimeout(function () {
						notice.addClass("lead");
					}, 0);
					setTimeout(function () {
						notice.addClass("success");
						notice.html(r.data.message);
					}, 600);

					setTimeout(function () {
						$(".merlin__body").addClass('js--finished');
					}, 1500);

					body.removeClass(drawer_opened);

					setTimeout(function () {
						$('.merlin__body').addClass('exiting');
					}, 3500);

					setTimeout(function () {
						window.location.href = btn.href;
					}, 4000);

				};
				do_ajax();
			}
		}
	}


	function ImportLists(btn) {

		var body = $('.merlin__body');
		var complete;
		var items_completed = 0;
		var current_offset = 0;
		var current_limit = 100;
		var current_item = "";
		var $current_node;
		var current_item_hash = "";
		var current_status;


		$(".merlin__drawer--install-plugins").addClass("installing");
		$(".merlin__drawer--install-plugins").find("input").prop("disabled", true);

		current_status = $('input[name="options"]:checked').map(function () {
			return $(this).val()
		}).get();

		find_next();

		function ajax_callback(response) {

			var currentSpan = $current_node.find("label");

			if (!response.data.added) {
				currentSpan.addClass("success");
				current_offset = 0;
				find_next();
			} else {
				current_offset += current_limit;
				process_current();
			}

			return;
			if (typeof response === "object" && typeof response.message !== "undefined") {
				currentSpan.removeClass('installing success error').addClass(response.message.toLowerCase());

				// The plugin is done (installed, updated and activated).
				if (typeof response.done != "undefined" && response.done) {
					find_next();
				} else if (typeof response.url != "undefined") {
					// we have an ajax url action to perform.
					if (response.hash == current_item_hash) {
						currentSpan.removeClass('installing success').addClass("error");
						find_next();
					} else {
						current_item_hash = response.hash;
						jQuery.post(response.url, response, ajax_callback).fail(ajax_callback);
					}
				} else {
					// error processing this plugin
					find_next();
				}
			} else {
				// The TGMPA returns a whole page as response, so check, if this plugin is done.
				process_current();
			}
		}

		function process_current() {
			if (current_item) {

				var $check = $current_node.find("input:checkbox");
				var currentSpan = $current_node.find("label");
				currentSpan.removeClass('installing success error');
				if ($check.is(":checked")) {
					currentSpan.addClass('installing');
					jQuery.post(merlin_params.ajaxurl, {
						action: "mailster_mailchimp_import_list",
						wpnonce: merlin_params.wpnonce,
						id: current_item,
						offset: current_offset,
						limit: current_limit,
						status: current_status,
					}, ajax_callback).fail(ajax_callback);
				} else {
					$current_node.addClass("skipping");
					setTimeout(find_next, 300);
				}
			}
		}

		function find_next() {
			if ($current_node) {
				if (!$current_node.data("done_item")) {
					items_completed++;
					$current_node.data("done_item", 1);
				}
				$current_node.find(".spinner").css("visibility", "hidden");
			}
			var $li = $(".merlin__drawer--install-plugins.mailchimp-lists li:visible");
			$li.each(function () {
				var $item = $(this);

				if ($item.data("done_item")) {
					return true;
				}

				current_item = $item.data("id");
				if (!current_item) {
					return true;
				}
				$current_node = $item;
				process_current();
				return false;
			});
			if (items_completed >= $li.length) {
				// finished all plugins!
				complete();
			}
		}


		function complete() {

			setTimeout(function () {
				$(".merlin__body").addClass('js--finished');
			}, 1000);

			body.removeClass(drawer_opened);

			setTimeout(function () {
				$('.merlin__body').addClass('exiting');
			}, 3000);

			setTimeout(function () {
				window.location.href = btn.href;
			}, 3500);

		};


	}

});