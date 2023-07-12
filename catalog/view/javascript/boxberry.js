(function (window, document, undefined) {
    var init = function () {

        document.addEventListener('click', function (e) {
            if (e.target && (e.target instanceof HTMLElement) && e.target.getAttribute('data-boxberry-open') == 'true') {
                e.preventDefault();
                var selectPointLink = e.target;
                var $checkbox = $(selectPointLink).prevAll('.checkbox').first().find("[name='delivery']");
                if ($checkbox.attr('disabled')) {
                    e.stopPropagation();
                    return false;
                }
                $(selectPointLink).parents('.radio').first().find("[name='shipping_method']").first().prop('checked', true).trigger("change");
                (function (selectPointLink) {
                    var classname = selectPointLink.getAttribute('data-type');
                    var $region = document.getElementById('input-shipping-zone');
                    var $city = document.getElementById('input-shipping-city');
                    var city = '';
                    if ($('.simplecheckout').length) {
                        city = $('#shipping_address_zone_id option:selected').text() + ' ' + $('#shipping_address_city').val();
                    } else {
                        city = $city.value + ' ' +
                            $region.options[$region.selectedIndex].text || undefined;
                    }
                    var token = selectPointLink.getAttribute('data-boxberry-token');
                    //   var targetStart = selectedPointLink.getAttribute('data-boxberry-target-start');
                    var order_id = selectPointLink.getAttribute('data-order-id');
                    var weight = selectPointLink.getAttribute('data-boxberry-weight');
                    var width = selectPointLink.getAttribute('data-boxberry-width');
                    var height = selectPointLink.getAttribute('data-boxberry-height');
                    var depth = selectPointLink.getAttribute('data-boxberry-length');
                    var paymentSum = selectPointLink.getAttribute('data-paymentsum');
                    var orderSum = selectPointLink.getAttribute('data-ordersum');
                    var sucrh = selectPointLink.getAttribute('data-sucrh');
                    var api = selectPointLink.getAttribute('data-api-url');
                    if (selectPointLink.getAttribute('data-class') == 'boxberryDeliverySelf') {
                        paymentSum = orderSum;
                    } else {
                        paymentSum = 0;
                    }
                    var boxberryPointSelectedHandler = function (result) {
                        $.get("index.php?route=checkout/boxberry/selectIssuePoint&issue_point_id=" + encodeURIComponent(result.id) + "&prepaid=" + (!paymentSum ? 1 : 0),
                            function (data) {
                                if (data.skip) return;
                                $("#checkout_address_main_city").val(data.city);
                                $("#checkout_address_address_1").val(data.addr1);
                                if ($("input[name='shipping_address_same']").is(":checked")) {
                                    $("#checkout_customer_main_city").val(data.city);
                                    $("#checkout_customer_address_1").val(data.addr1);
                                }
                                $("#shipping_address_city").val(data.city);
                                $("#shipping_address_address_1").val('# ' + data.id + ' ' + data.addr1).attr('readonly', true);
                                if ($("input[name='address_same']").is(":checked")) {
                                    $("#payment_address_city").val(data.city);
                                    $("#payment_address_address_1").val('# ' + data.id + ' ' + data.addr1);
                                }
                                $("#input-shipping-city").val(data.city);
                                $("#input-shipping-address-1").val(data.addr1);

                                var sel_sm = $("input[name=shipping_method]:checked").val();

                                var shippingData = $("#collapse-shipping-address input[type='text'], " +
                                    "#collapse-shipping-address input[type='date'], " +
                                    "#collapse-shipping-address input[type='datetime-local'], " +
                                    "#collapse-shipping-address input[type='time'], " +
                                    "#collapse-shipping-address input[type='password'], " +
                                    "#collapse-shipping-address input[type='checkbox']:checked, " +
                                    "#collapse-shipping-address input[type='radio']:checked, " +
                                    "#collapse-shipping-address textarea, " +
                                    "#collapse-shipping-address select");

                                if ($('.simplecheckout-block-content').length) {
                                    shippingData = {
                                        "firstname": $('#shipping_address_firstname').val(),
                                        "lastname": $('#shipping_address_lastname').val(),
                                        "company": '',
                                        "address_1": $('#shipping_address_address_1').val(),
                                        "address_2": $('#shipping_address_address_1').val(),
                                        "city": $('#shipping_address_city').val(),
                                        "postcode": $('#shipping_address_postcode').val(),
                                        "country_id": $('#shipping_address_country_id').val(),
                                        "zone_id": $('#shipping_address_zone_id').val()
                                    };
                                }

                                $.ajax(
                                    {
                                        url: "index.php?route=checkout/boxberry/guestShippingSave",
                                        type: "post",
                                        data: shippingData,
                                        dataType: "json",
                                        success: function (json) {
                                            $.ajax(
                                                {
                                                    url: "index.php?route=checkout/boxberry/getIssuePoint",
                                                    dataType: "html",
                                                    success: function (html) {
                                                        $(selectPointLink).parents('.radio').first().html(html);
                                                        var selector = "input[value='" + sel_sm + "']";
                                                        $(selector).prop("checked", true);
                                                    }
                                                }
                                            );
                                        }
                                    });
                            });
                    };
                    boxberry.versionAPI(api);
                    boxberry.sucrh(sucrh);
                    boxberry.open(boxberryPointSelectedHandler, token, city, 0, orderSum, weight, paymentSum,
                        height, width, depth);
                })(selectPointLink);
            }
        }, true);
    };
    init();
})(window, document, undefined);