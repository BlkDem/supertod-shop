<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-boxberry" data-toggle="tooltip" title="<?php echo $button_save; ?>"
                        class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i
                            class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach($breadcrumbs as $breadcrumb): ?>
                    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if($error_warning): ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-boxberry"
                      class="form-horizontal">
                    <div class="row">
                        <div class="col-sm-2">
                            <ul class="nav nav-pills nav-stacked">
                                <li class="active">
                                    <a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a>
                                </li>

                            </ul>
                        </div>
                        <div class="col-sm-10">
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab-general">
                                    <table class="table table-bordered">

                                        <tr>
                                            <td><?php echo $text_api_token; ?></td>
                                            <td><input type="text" name="shipping_boxberry_api_token"
                                                       value="<?php echo $shipping_boxberry_api_token;?>"/></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $text_api_url; ?></td>
                                            <td><input type="text" name="shipping_boxberry_api_url"
                                                       value="<?php echo $shipping_boxberry_api_url; ?>"/></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $text_widget_url; ?></td>
                                            <td><input type="text" name="shipping_boxberry_widget_url"
                                                       value="<?php echo $shipping_boxberry_widget_url; ?>"/></td>
                                        </tr>

                                        <tr>
                                            <td><?php echo $text_weight; ?></td>
                                            <td><input type="text" name="shipping_boxberry_weight"
                                                       value="<?php echo $shipping_boxberry_weight; ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $text_weight_min; ?></td>
                                            <td><input type="text" name="shipping_boxberry_weight_min"
                                                       value="<?php echo $shipping_boxberry_weight_min; ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $text_weight_max; ?></td>
                                            <td><input type="text" name="shipping_boxberry_weight_max"
                                                       value="<?php echo $shipping_boxberry_weight_max; ?>"/>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><?php echo $text_status; ?></td>
                                            <td><select name="shipping_boxberry_status">
                                                    <?php if($shipping_boxberry_status): ?>
                                                        <option value="1"
                                                                selected="selected"><?php echo $text_enabled; ?></option>
                                                        <option value="0"><?php echo $text_disabled; ?></option>
                                                    <?php else: ?>
                                                        <option value="1"><?php echo $text_enabled ; ?></option>
                                                        <option value="0"
                                                                selected="selected"><?php echo $text_disabled ; ?></option>
                                                    <?php endif; ?>
                                                </select></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $text_sort_order; ?></td>
                                            <td><input type="text" name="shipping_boxberry_sort_order"
                                                       value="<?php echo $shipping_boxberry_sort_order; ?>" size="1"/></td>
                                        </tr>

                                        <tr>
                                            <td><?php echo $text_order_status; ?></td>
                                            <td>
                                                <select name="shipping_boxberry_order_status">
                                                    <?php foreach($order_statuses as $order_status) : ?>
                                                        <?php if($order_status['order_status_id'] == $shipping_boxberry_order_status): ?>
                                                            <option value="<?php echo $order_status['order_status_id']?>"
                                                                    selected="selected"><?php echo $order_status['name']; ?></option>
                                                        <?php else: ?>
                                                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name'];?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><?php echo $text_weight_id; ?></td>
                                            <td>
                                                <select name="shipping_boxberry_weight_class_id">
                                                    <?php foreach($weight_ids as $weight_id) : ?>
                                                        <?php if($weight_id['weight_class_id'] == $shipping_boxberry_weight_class_id) : ?>
                                                            <option value="<?php echo $weight_id['weight_class_id']; ?>"
                                                                    selected="selected"><?php echo $weight_id['title']?></option>
                                                        <?php else: ?>
                                                            <option value="<?php echo $weight_id['weight_class_id']; ?>"><?php echo $weight_id['title']; ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $text_length_id; ?></td>
                                            <td>
                                                <select name="shipping_boxberry_length_class_id">
                                                    <?php foreach($length_ids as $length_id) : ?>
                                                        <?php if($length_id['length_class_id'] == $shipping_boxberry_length_class_id) : ?>
                                                            <option value="<?php echo $length_id['length_class_id']; ?>"
                                                                    selected="selected"><?php echo $length_id['title']; ?></option>
                                                        <?php else: ?>
                                                            <option value="<?php echo $length_id['length_class_id']; ?>"><?php echo $length_id['title']; ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><?php echo $text_pickup_status; ?></td>
                                            <td><?php echo $text_status; ?> <select name="shipping_boxberry_pickup_status">
                                                    <?php if($shipping_boxberry_pickup_status): ?>
                                                        <option value="1"
                                                                selected="selected"><?php echo $text_enabled ; ?></option>
                                                        <option value="0"><?php echo $text_disabled ; ?></option>
                                                    <?php else: ?>
                                                        <option value="1"><?php echo $text_enabled; ?></option>
                                                        <option value="0"
                                                                selected="selected"><?php echo $text_disabled; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_sucrh; ?> <select name="shipping_boxberry_pickup_sucrh">
                                                    <?php if($shipping_boxberry_pickup_sucrh) : ?>
                                                        <option value="1"
                                                                selected="selected"><?php echo $text_sucrh_yes; ?></option>
                                                        <option value="0"><?php echo $text_sucrh_no; ?></option>
                                                    <?php else: ?>
                                                        <option value="1"><?php echo $text_sucrh_yes; ?></option>
                                                        <option value="0"
                                                                selected="selected"><?php echo $text_sucrh_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_parselcreate_auto; ?> <select name="shipping_boxberry_pickup_parselcreate_auto">
                                                    <?php if($shipping_boxberry_pickup_parselcreate_auto) : ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_parselcreate_auto_yes; ?></option>
                                                    <option value="0"><?php echo $text_parselcreate_auto_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_parselcreate_auto_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_parselcreate_auto_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_parselsend_auto; ?> <select name="shipping_boxberry_pickup_parselsend_auto">
                                                    <?php if($shipping_boxberry_pickup_parselsend_auto) : ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_parselsend_auto_yes; ?></option>
                                                    <option value="0"><?php echo $text_parselsend_auto_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_parselsend_auto_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_parselsend_auto_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p><?php echo $text_name; ?><input name="shipping_boxberry_pickup_name"
                                                                                 style="width: 80%;"
                                                                                 value="<?php echo $shipping_boxberry_pickup_name; ?>"
                                                                                 type="text">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><?php echo $text_pickup_prepaid_status; ?></td>
                                            <td><?php echo $text_status; ?><select name="shipping_boxberry_pickup_prepaid_status">
                                                    <?php if($shipping_boxberry_pickup_prepaid_status): ?>
                                                        <option value="1"
                                                                selected="selected"><?php echo $text_enabled; ?></option>
                                                        <option value="0"><?php echo $text_disabled; ?></option>
                                                    <?php else: ?>
                                                        <option value="1"><?php echo $text_enabled; ?></option>
                                                        <option value="0"
                                                                selected="selected"><?php echo $text_disabled; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_sucrh; ?> <select name="shipping_boxberry_pickup_prepaid_sucrh">
                                                    <?php if($shipping_boxberry_pickup_prepaid_sucrh) : ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_sucrh_yes; ?></option>
                                                    <option value="0"><?php echo $text_sucrh_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_sucrh_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_sucrh_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_parselcreate_auto; ?> <select name="shipping_boxberry_pickup_prepaid_parselcreate_auto">
                                                    <?php if($shipping_boxberry_pickup_prepaid_parselcreate_auto) : ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_parselcreate_auto_yes; ?></option>
                                                    <option value="0"><?php echo $text_parselcreate_auto_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_parselcreate_auto_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_parselcreate_auto_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_parselsend_auto; ?> <select name="shipping_boxberry_pickup_prepaid_parselsend_auto">
                                                    <?php if($shipping_boxberry_pickup_prepaid_parselsend_auto) : ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_parselsend_auto_yes; ?></option>
                                                    <option value="0"><?php echo $text_parselsend_auto_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_parselsend_auto_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_parselsend_auto_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p><?php echo $text_name; ?><input
                                                        name="shipping_boxberry_pickup_prepaid_name" style="width: 80%;"
                                                        value="<?php echo $shipping_boxberry_pickup_prepaid_name; ?>" type="text">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><?php echo $text_courier_delivery_status; ?></td>
                                            <td><?php echo $text_status; ?><select
                                                        name="shipping_boxberry_courier_delivery_status">
                                                    <?php if($shipping_boxberry_courier_delivery_status) : ?>
                                                        <option value="1"
                                                                selected="selected"><?php echo $text_enabled; ?></option>
                                                        <option value="0"><?php echo $text_disabled; ?></option>
                                                    <?php else: ?>
                                                        <option value="1"><?php echo $text_enabled; ?></option>
                                                        <option value="0"
                                                                selected="selected"><?php echo $text_disabled; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_sucrh; ?> <select name="shipping_boxberry_courier_delivery_sucrh">
                                                    <?php if($shipping_boxberry_courier_delivery_sucrh): ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_sucrh_yes; ?></option>
                                                    <option value="0"><?php echo $text_sucrh_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_sucrh_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_sucrh_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_parselcreate_auto; ?> <select name="shipping_boxberry_courier_delivery_parselcreate_auto">
                                                    <?php if($shipping_boxberry_courier_delivery_parselcreate_auto): ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_parselcreate_auto_yes; ?></option>
                                                    <option value="0"><?php echo $text_parselcreate_auto_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_parselcreate_auto_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_parselcreate_auto_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_parselsend_auto; ?> <select name="shipping_boxberry_courier_delivery_parselsend_auto">
                                                    <?php if($shipping_boxberry_courier_delivery_parselsend_auto) : ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_parselsend_auto_yes; ?></option>
                                                    <option value="0"><?php echo $text_parselsend_auto_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_parselsend_auto_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_parselsend_auto_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p><?php echo $text_name; ?><input
                                                        name="shipping_boxberry_courier_delivery_name"
                                                        style="width: 80%;"
                                                        value="<?php echo $shipping_boxberry_courier_delivery_name; ?>"
                                                        type="text">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><?php echo $text_courier_delivery_prepaid_status; ?></td>
                                            <td><?php echo $text_status; ?><select
                                                        name="shipping_boxberry_courier_delivery_prepaid_status">
                                                    <?php if($shipping_boxberry_courier_delivery_prepaid_status): ?>
                                                        <option value="1"
                                                                selected="selected"><?php echo $text_enabled; ?></option>
                                                        <option value="0"><?php echo $text_disabled; ?></option>
                                                    <?php else: ?>
                                                        <option value="1"><?php echo $text_enabled; ?></option>
                                                        <option value="0"
                                                                selected="selected"><?php echo $text_disabled; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_sucrh; ?> <select name="shipping_boxberry_courier_delivery_prepaid_sucrh">
                                                    <?php if($shipping_boxberry_courier_delivery_prepaid_sucrh): ?>
                                                        <option value="1"
                                                                selected="selected"><?php echo $text_sucrh_yes; ?></option>
                                                        <option value="0"><?php echo $text_sucrh_no; ?></option>
                                                    <?php else: ?>
                                                        <option value="1"><?php echo $text_sucrh_yes; ?></option>
                                                        <option value="0"
                                                                selected="selected"><?php echo $text_sucrh_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_parselcreate_auto; ?> <select name="shipping_boxberry_courier_delivery_prepaid_parselcreate_auto">
                                                    <?php if($shipping_boxberry_courier_delivery_prepaid_parselcreate_auto): ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_parselcreate_auto_yes; ?></option>
                                                    <option value="0"><?php echo $text_parselcreate_auto_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_parselcreate_auto_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_parselcreate_auto_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p>
                                                <?php echo $text_parselsend_auto; ?> <select name="shipping_boxberry_courier_delivery_prepaid_parselsend_auto">
                                                    <?php if($shipping_boxberry_courier_delivery_prepaid_parselsend_auto) : ?>
                                                    <option value="1"
                                                            selected="selected"><?php echo $text_parselsend_auto_yes; ?></option>
                                                    <option value="0"><?php echo $text_parselsend_auto_no; ?></option>
                                                    <?php else: ?>
                                                    <option value="1"><?php echo $text_parselsend_auto_yes; ?></option>
                                                    <option value="0"
                                                            selected="selected"><?php echo $text_parselsend_auto_no; ?></option>
                                                    <?php endif; ?>
                                                </select>
                                                <p></p><?php echo $text_name; ?><input
                                                        name="shipping_boxberry_courier_delivery_prepaid_name"
                                                        style="width: 80%;"
                                                        value="<?php echo $shipping_boxberry_courier_delivery_prepaid_name; ?>"
                                                        type="text">
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>