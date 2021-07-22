(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Objects manipulates with UI of the plugin
         * 
         * @since    1.0.0
         */
        var MviUI = {

            /**
             * If set to true form will be saved as a template
             * 
             * @since    1.0.0
             */
            save_form: false,

            /**
             * Main call
             * 
             * @since    1.0.0
             */
            init: function() {

                // Type changes
                $('#mvi-post-type').change(this.type_change);
                $('.mvi-provider').change(this.type_change);

                // Clicks
                $('.mvi-delete-template').click(this.ajax_delete_template);

                // On form submit
                $('#mvi-video-import').submit(this.import);

                // Events
                $(document).on('meks-video-importer-notify', this.notify);
                $(document).on('meks-video-importer-show-form', this.show_form);

                // Button for submitting form
                $('#mvi-import-posts, #mvi-save-and-import-posts').click(function() {
                    MviUI.save_form = $(this).data('save');
                    return;
                });

                this.suggest_taxonomies_init();
            },

            /**
             * Add WordPress autocomplete for not hierarchical taxonomies because hierarchical have checkboxes
             *
             * @since    1.0.0
             */
            suggest_taxonomies_init: function() {
                var $not_hierarchical = $('.mvi-not-hierarchical > input[type=text]');

                if (mvi_empty($not_hierarchical))
                    return;

                $not_hierarchical.each(function(i, elem) {
                    var $elem = $(elem),
                        taxonomy = $elem.data('name');

                    $elem.suggest(meks_video_importer_script.ajax_url + "?action=ajax-tag-search&tax=" + taxonomy, {
                        multiple: true,
                        multipleSep: ","
                    });
                });
            },

            /**
             * Prints messages
             *
             * @param e - "meks-video-importer-notify"
             * @param $elem - This element will have messages printed
             * @param messages
             * @since    1.0.0
             */
            notify: function(e, $elem, messages) {
                $elem.html('');
                $.each(messages, function(i, messageObj) {
                    $elem.append('<div class="mvi-notice mvi-' + messageObj.type + '"><p>' + messageObj.msg + '</p></div>');
                });
            },

            /**
             * Form is initially displayed none this function is called on event "meks-video-importer-show-form"
             * 
             * @since    1.0.0
             */
            show_form: function() {
                $('#mvi-video-import').show();
            },

            /**
             * Helper for type change, depending on type change it shows element, they are connected using data-type
             *
             * @since    1.0.0
             */
            type_change: function() {
                var $this = $(this),
                    type = $this.data('type'),
                    val = $this.val();

                $('.type-change.' + type).hide();
                $('.type-change.' + type + '.active').removeClass('active');
                $('.type-change.' + type + '.' + val).show();
            },

            /**
             * Ajax for importing posts
             * 
             * @param e
             * @since    1.0.0
             */
            import: function(e) {
                e.preventDefault();
                var $this = $(this),
                    data = MviUI.get_data_and_maybe_ajax_save($this),
                    itemData = meks_video_importer_script.hidden_fields,
                    $items = $('#mvi-fetched tbody > tr');

                if (mvi_empty(data))
                    return;

                data.provider = $('#mvi-video-fetch .mvi-provider:checked').val();

                MviUI.go_to_top_of($("#the-list .check-column > input[type=checkbox]:checked:first-child"));

                $items.each(function(i, item) {
                    var $item = $(item),
                        $checkbox = $item.find('.check-column > input[type="checkbox"]'),
                        checked = $checkbox.is(':checked'),
                        $messages = $item.find('.status .mvi-status-messages');

                    if (!checked)
                        return true;

                    $(document).trigger('meks-video-importer-notify', [$messages, [{
                        type: 'default',
                        msg: meks_video_importer_script.importing
                    }]]);

                    data['mvi-video-id'] = $checkbox.val();

                    $.each(itemData, function(i, data_name) {
                        data['mvi-video-' + data_name] = $item.find('.mvi-video-' + data_name).val();
                    });

                    $.post(
                        meks_video_importer_script.ajax_url,
                        data,
                        function(response) {
                            if (response.success)
                                $checkbox.removeAttr('checked');

                            $(document).trigger('meks-video-importer-notify', [$messages, response.data]);
                        }
                    );
                });

            },

            /**
             * Helper for formatting data for inserting posts with ajax
             *
             * @param $form
             * @returns {{action: string, cache: boolean, async: boolean, security: *}}
             * @since    1.0.0
             */
            get_data_and_maybe_ajax_save: function($form) {
                var data = {
                    action: 'mvi_import_post',
                    cache: false,
                    async: false,
                    security: $("#_wpnonce").val()
                };

                $.extend(data, MviUI.extract_form($form));

                delete(data['mvi-items']);
                delete(data['id']);

                if (MviUI.save_form) {
                    var $template_id = $('#template_id'),
                        $template_name = $('#template_name'),
                        name = $('#template_name').val(),
                        id = !(mvi_empty($template_id)) ? $template_id.val() : 0,
                        $fetch = $('#mvi-video-fetch');

                    if (mvi_empty(name)) {
                        $template_name.focus();
                        return null;
                    }

                    data.id = id;
                    data.name = name;
                    $.extend(data, MviUI.extract_form($fetch));

                    $.post(
                        meks_video_importer_script.ajax_url, {
                            action: 'mvi_save_template',
                            data: data
                        },
                        function(response) {
                            $(document).trigger('meks-video-importer-notify', [$('#save-and-import-messages'), [response.data]]);
                        }
                    );
                }

                return data;
            },

            /**
             * Helper for getting to top of some element
             *
             * @param $elem
             * @since    1.0.0
             */
            go_to_top_of: function($elem) {
                if (mvi_empty($elem))
                    return;

                $('html, body').animate({
                    scrollTop: $elem.offset().top - 50
                }, 1000);
            },

            /**
             * Helper extracting data from submitted form
             *
             * @param $form
             * @returns {{}}
             * @since    1.0.0
             */
            extract_form: function($form) {
                var formObj = {};
                var inputs = $form.serializeArray();

                $.each(inputs, function(i, input) {

                    var name = input.name,
                        value = input.value,
                        sub_name = input.name.match(/\[(.*?)\]/);

                    if (sub_name) {
                        var tax = name.replace(sub_name[0], ''),
                            val_obj = {};

                        val_obj[sub_name[1]] = value;
                        if (!mvi_empty(formObj[tax])) {

                            if (!mvi_empty(formObj[tax][sub_name[1]])) {
                                formObj[tax][sub_name[1]] = formObj[tax][sub_name[1]] + ',' + value;
                                return true;
                            }

                            formObj[tax][sub_name[1]] = value;
                            return true;
                        }

                        formObj[tax] = val_obj;
                        return true;
                    }

                    if (formObj.hasOwnProperty(name) && value != 'on') {
                        formObj[name] = formObj[name] + ',' + value;
                    } else {
                        formObj[name] = value;
                    }

                });

                return formObj;
            },

            /**
             * Helper for printing videos table
             * 
             * @param html
             * @since    1.0.0
             */
            append_videos: function(html) {
                $('#mvi-fetched').html(html);
                $('#mvi-video-import .wp-list-table').show();
                var $trs = $(html).find('tbody tr'),
                    count = 0;

                if(!$trs.hasClass('no-items'))
                    count = $trs.length;

                $('#mvi-fetched').prepend('<p class="mvi-total-count">' + meks_video_importer_script.total + ': <span>' + count + '</span></p>');
            },

            /**
             * Delete template from templates
             * 
             * @param e
             * @returns {boolean}
             * @since    1.0.0
             */
            ajax_delete_template: function(e) {
                e.preventDefault();
                var $this = $(this),
                    $parent = $this.parent(),
                    id = $this.data('id'),
                    areYouSure = confirm(meks_video_importer_script.are_you_sure);

                if (mvi_empty(id) || !areYouSure) {
                    return false;
                }

                $.post(
                    meks_video_importer_script.ajax_url, {
                        'action': 'mvi_delete_template',
                        'id': id
                    },
                    function(response) {

                        $(document).trigger('meks-video-importer-notify', [$this.find('.meks-video-importer-delete-template-message'), [response.data]]);
                        if (response.success) {
                            setTimeout(function() {
                                $parent.fadeOut(function() {
                                    $parent.remove();
                                });
                            }, 3000);
                        }
                    }
                );
            }
        };
        MviUI.init();

        /**
         * Works with Youtube Api
         *
         * @since    1.0.0
         */
        var YouTube = {

            /**
             * Contains status of credentials verification process, basically if ajax is working or not
             *
             * @since    1.0.0
             */
            workingOnVerifying: false,

            /**
             * Main call
             *
             * @since    1.0.0
             */
            init: function() {

                // Clicks & changes
                $('#mvi-fetch-youtube').click(this.ajax_fetch);
                $('#mvi-youtube-api-key').on('keyup, keydown, paste, change', this.ajax_verify_access_credentials);
                $('#mvi-youtube-type').change(MviUI.type_change);

                // Events
                $(document).on('meks-video-importer-youtube-loader-on', this.show_loader);
                $(document).on('meks-video-importer-youtube-loader-off', this.hide_loader);

                // If template is isset in url trigger click for
                if (!mvi_empty(mvi_get_url_var('template')))
                    $('#mvi-fetch-youtube').trigger('click');
            },

            /**
             * Verify credentials for working with API
             *
             * @param e
             * @since    1.0.0
             */
            ajax_verify_access_credentials: function(e) {
                e.preventDefault();

                var $this = $(this),
                    val = $this.val();

                $(document).trigger('meks-video-importer-youtube-loader-on');

                if (YouTube.workingOnVerifying)
                    return;

                YouTube.workingOnVerifying = true;

                $.post(
                    meks_video_importer_script.ajax_url, {
                        action: 'mvi_save_youtube_settings',
                        key: val
                    },
                    function(response) {

                        $(document).trigger('meks-video-importer-notify', [$('#mvi-youtube-api-verify-message'), [{
                            type: response.success ? 'success' : 'error',
                            msg: response.data.message
                        }]]);

                        $(document).trigger('meks-video-importer-youtube-loader-off');

                        YouTube.workingOnVerifying = false;
                    }
                );
            },

            /**
             * Show loader on the side of fetch button
             *
             * @since    1.0.0
             */
            show_loader: function() {
                $('#mvi-youtube-loader').css('visibility', 'visible');
            },

            /**
             * Hide loader on the side of fetch button
             *
             * @since    1.0.0
             */
            hide_loader: function() {
                $('#mvi-youtube-loader').css('visibility', 'hidden');
            },

            /**
             * Get Youtube videos
             *
             * @param e
             * @since    1.0.0
             */
            ajax_fetch: function(e) {
                e.preventDefault();

                // When template is set and this function is called initially quit if youtube is not provider
                if ($('#mvi-video-fetch input[name=provider]:checked').val() !== 'youtube')
                    return;

                var $type = $('#mvi-youtube-type'),
                    type = $type.val(),
                    $id = $('#mvi-youtube-id'),
                    id = $id.val(),
                    $messages = $('#youtube-messages');

                $messages.html('');

                if (!id.length || !type.length) {
                    $(document).trigger('meks-video-importer-notify', [$messages, [{
                        type: 'error',
                        msg: meks_video_importer_youtube.empty_id_or_type
                    }]]);
                    return;
                }

                $(document).trigger('meks-video-importer-youtube-loader-on');

                var data = {
                    action: 'mvi_fetch_from_youtube',
                    type: type,
                    id: id
                };

                $.post(
                    meks_video_importer_script.ajax_url,
                    data,
                    function(response) {

                        if (response.success) {
                            $(document).trigger('meks-video-importer-show-form');
                            $(document).trigger('meks-video-importer-youtube-loader-off');

                            if ($.isEmptyObject(response.data.table))
                                return;

                            MviUI.append_videos(response.data.table);
                            return;
                        }

                        $(document).trigger('meks-video-importer-notify', [$messages, [{
                            type: 'error',
                            msg: response.data.message
                        }]]);
                        $(document).trigger('meks-video-importer-youtube-loader-off');
                    }
                );
            }
        };

        YouTube.init();

        /**
         * Works with Vimeo Api
         *
         * @since    1.0.0
         */
        var Vimeo = {

            /**
             * Contains status of credentials verification process, basically if ajax is working or not
             *
             * @since    1.0.0
             */
            workingOnVerifying: false,

            /**
             * Main call
             *
             * @since    1.0.0
             */
            init: function() {

                // Clicks & changes
                $('#mvi-fetch-vimeo').click(this.ajax_fetch);
                $('#mvi-vimeo-client-id, #mvi-vimeo-client-secret').on('keyup, keydown, paste, change', this.ajax_verify_access_credentials);
                $('#mvi-vimeo-type').change(MviUI.type_change);

                // Events
                $(document).on('meks-video-importer-vimeo-loader-on', this.show_loader);
                $(document).on('meks-video-importer-vimeo-loader-off', this.hide_loader);

                // If template is isset in url trigger click for
                if (!mvi_empty(mvi_get_url_var('template'))){
                    $('#mvi-fetch-vimeo').trigger('click');
                }
            },

            /**
             * Verify credentials for working with API
             *
             * @param e
             * @since    1.0.0
             */
            ajax_verify_access_credentials: function(e) {
                e.preventDefault();

                var id = $('#mvi-vimeo-client-id').val(),
                    secret = $('#mvi-vimeo-client-secret').val();


                if( mvi_empty(id) ||  mvi_empty(secret) ){
                    return false;
                }

                $(document).trigger('meks-video-importer-vimeo-loader-on');

                if (Vimeo.workingOnVerifying){
                    return false;
                }

                YouTube.workingOnVerifying = true;

                $.post(
                    meks_video_importer_script.ajax_url, {
                        action: 'mvi_save_vimeo_settings',
                        id: id,
                        secret: secret
                    },
                    function(response) {

                        $(document).trigger('meks-video-importer-notify', [$('#mvi-vimeo-api-verify-message'), [{
                            type: response.success ? 'success' : 'error',
                            msg: response.data.message
                        }]]);

                        $(document).trigger('meks-video-importer-vimeo-loader-off');

                        Vimeo.workingOnVerifying = false;
                    }
                );
            },

            /**
             * Show loader on the side of fetch button
             */
            show_loader: function() {
                $('#mvi-vimeo-loader').css('visibility', 'visible');
            },

            /**
             * Hide loader on the side of fetch button
             */
            hide_loader: function() {
                $('#mvi-vimeo-loader').css('visibility', 'hidden');
            },

            /**
             * Get Vimeo videos
             *
             * @param e
             */
            ajax_fetch: function(e) {
                e.preventDefault();

                // When template is set and this function is called initially quit if vimeo is not provider
                if ($('#mvi-video-fetch input[name=provider]:checked').val() !== 'vimeo')
                    return;

                var $type = $('#mvi-vimeo-type'),
                    type = $type.val(),
                    $id = $('#mvi-vimeo-id'),
                    id = $id.val(),
                    $from = $('#mvi-vimeo-from-page'),
                    from = $from.val(),
                    $to = $('#mvi-vimeo-to-page'),
                    to = $to.val(),
                    $messages = $('#vimeo-messages');

                $messages.html('');

                if (!id.length || !type.length) {
                    $(document).trigger('meks-video-importer-notify', [$messages, [{
                        type: 'error',
                        msg: meks_video_importer_vimeo.empty_id_or_type
                    }]]);
                    return;
                }

                $(document).trigger('meks-video-importer-vimeo-loader-on');

                var data = {
                    action: 'mvi_fetch_from_vimeo',
                    type: type,
                    id: id,
                    from: !mvi_empty(from) ? from : null,
                    to: !mvi_empty(to) ? to : null
                };

                $.post(
                    meks_video_importer_script.ajax_url,
                    data,
                    function(response) {

                        if (response.success) {
                            $(document).trigger('meks-video-importer-show-form');
                            $(document).trigger('meks-video-importer-vimeo-loader-off');

                            if ($.isEmptyObject(response.data.table))
                                return;

                            MviUI.append_videos(response.data.table);
                            return;
                        }

                        $(document).trigger('meks-video-importer-notify', [$messages, [{
                            type: 'error',
                            msg: response.data.message
                        }]]);
                        $(document).trigger('meks-video-importer-vimeo-loader-off');
                    }
                );
            }

        };

        Vimeo.init();

    });


    /**
     * Checks if variable is empty or not
     *
     * @param variable
     * @returns {boolean}
     */
    function mvi_empty(variable) {
        return (typeof variable === 'undefined' || variable === null || variable.length === 0 || variable === "");
    }

    /**
     * Get url parameter like $_GET in php
     *
     * @returns {Array}
     */
    function mvi_get_url_vars() {
        var vars = [],
            hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }

    /**
     * Get single url var like $_GET['name'] in php
     *
     * @param name
     * @returns {*}
     */
    function mvi_get_url_var(name) {
        return mvi_get_url_vars()[name];
    }

})(jQuery);