<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-left">
                <h1><?php echo $heading_title; ?></h1>
                <ul class="breadcrumb">
                    <?php foreach ($breadcrumbs as $breadcrumb): ?>
                        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="container-fluid"><?php if($error_warning): ?>
                <div class="alert alert-danger alert-dismissible"><i
                            class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            <div class="row">

                <div id="filter-order" class="col-md-3 col-md-push-9 col-sm-12 hidden-sm hidden-xs">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-filter"></i> <?php echo $text_filter; ?></h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="control-label" for="input-order-id"><?php echo $entry_order_id; ?></label>
                                <input type="text" name="filter_order_id" value="<?php echo $filter_order_id; ?>"
                                       placeholder="<?php echo $entry_order_id; ?>" id="input-order-id" class="form-control"/>
                            </div>
                            <div class="form-group text-right">
                                <button type="button" id="button-filter" class="btn btn-default"><i
                                            class="fa fa-filter"></i> <?php echo $button_filter; ?></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-9 col-md-pull-3 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
                        </div>
                        <div class="panel-body">
                            <form method="post" action="" enctype="multipart/form-data" id="form-order">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <td style="width: 1px;" class="text-center"><input type="checkbox"
                                                                                               onclick="$('input[name*=\'selected\']').prop('checked', this.checked);"/>
                                            </td>
                                            <td class="text-left"><?php if($sort == 'order_id'): ?> <a
                                                        href="<?php echo $sort_order_id; ?>"
                                                        class="<?php echo $order; ?>"><?php echo $column_order_id; ?></a> <?php else: ?>
                                                    <a href="<?php echo $sort_order_id; ?>"><?php echo $column_order_id; ?></a> <?php endif; ?>
                                            </td>
                                            <td class="text-left"><?php if($sort == 'im_id'): ?> <a href="<?php echo $sort_im_id; ?>"
                                                                                              class="<?php echo $order; ?>"><?php echo $column_im_id; ?></a> <?php else: ?>
                                                    <a href="<?php echo $sort_im_id; ?>"><?php echo $column_im_id; ?></a> <?php endif; ?></td>
                                            <td class="text-left"><?php if($sort == 'label'): ?> <a href="<?php echo $sort_label; ?>"
                                                                                              class="<?php echo $order; ?>"><?php echo $column_label; ?></a> <?php else: ?>
                                                    <a href="<?php echo $sort_label; ?>"><?php echo $column_label; ?></a> <?php endif; ?></td>
                                            <td class="text-left"><?php if($sort == 'boxberry_to_point'): ?> <a
                                                        href="<?php echo $sort_boxberry_to_point; ?>"
                                                        class="<?php echo $order; ?>"><?php echo $column_boxberry_to_point; ?></a> <?php else: ?>
                                                    <a href="<?php echo $sort_boxberry_to_point; ?>"><?php echo $column_boxberry_to_point; ?></a> <?php endif; ?>
                                            </td>
                                            <td class="text-left"><?php if($sort == 'address'): ?> <a
                                                        href="<?php echo $sort_address; ?>"
                                                        class="<?php echo $order; ?>"><?php echo $column_address; ?></a> <?php else: ?> <a
                                                        href="<?php echo $sort_address; ?>"><?php echo $column_address; ?></a> <?php endif; ?>
                                            </td>

                                            <td class="text-left"><?php if($sort == 'error'): ?> <a href="<?php echo $sort_error; ?>"
                                                                                              class="<?php echo mb_strtolower($order); ?>"><?php echo $column_error; ?></a> <?php else: ?>
                                                    <a href="<?php echo $sort_error; ?>"><?php echo $column_error; ?></a> <?php endif; ?></td>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        <?php if(!empty($deliveries)): ?>
                                            <?php foreach($deliveries as $delivery): ?>
                                                <tr>
                                                    <td class="text-center"> <?php if(in_array($delivery['order_id'], $selected)): ?>
                                                            <input type="checkbox" name="selected[]"
                                                                   value="<?php echo $delivery['order_id']; ?>" checked="checked"/>
                                                        <?php else: ?>
                                                            <input type="checkbox" name="selected[]"
                                                                   value="<?php echo $delivery['order_id']; ?>"/>
                                                        <?php endif; ?>
                                                    <td class="text-right"><?php echo $delivery['order_id']; ?></td>
                                                    <td class="text-left">
                                                        <?php if(!empty($delivery['im_id'])) : ?>
                                                        <?php $tracking_link = explode(' ', $delivery['im_id']) ?>
                                                        <?php if(!empty($tracking_link[1])) : ?>
                                                        <?php echo $tracking_link[0]; ?><p></p>
                                                            <a target="_blank" href="<?php echo $tracking_link[1]; ?>">Ссылка на отслеживание</a>
                                                        <?php else: ?>
                                                            <?php echo $delivery['im_id']; ?>
                                                        <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-left">
                                                        <?php if(!empty($delivery['label'])) : ?>
                                                        <?php $new_label = explode(' ', $delivery['label']) ?>
                                                        <?php if(!empty($new_label[0])) : ?>
                                                            <a target="_blank" href="<?php echo $new_label[0]; ?>">Скачать
                                                                этикетку</a>
                                                        <?php else: ?>
                                                        <a target="_blank" href="<?php echo $delivery['label']; ?>">Скачать
                                                            этикетку</a>
                                                        <?php endif; ?>
                                                        <?php endif; ?>
                                                        <?php if(!empty($delivery['label'])) : ?>
                                                        <?php $act_label = explode(' ', $delivery['label']) ?>
                                                        <?php if(!empty($act_label[1])) : ?>
                                                        <p></p>
                                                        <a target="_blank" href="<?php echo $act_label[1]; ?>">Скачать
                                                            акт</a>
                                                        <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-left"><?php echo $delivery['boxberry_to_point']; ?></td>
                                                    <td class="text-left">
                                                        <?php echo $delivery['address']; ?>
                                                    </td>
                                                    <td class="text-left"><?php echo $delivery['error']; ?></td>

                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>

                                    </table>
                                </div>
                            </form>
                            <div class="row">
                                <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
                                <div class="col-sm-6 text-right"><?php echo $results; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script type="text/javascript"><!--
                $('#button-filter').on('click', function () {
                    url = '';
                    var filter_order_id = $('input[name=\'filter_order_id\']').val();
                    if (filter_order_id) {
                        url += '&filter_order_id=' + encodeURIComponent(filter_order_id);
                    }
                    location = 'index.php?route=sale/boxberry&<?php echo (strpos(VERSION, '2.') !== false ? 'token' : 'user_token') ;?>=<?php echo (strpos(VERSION, '2.') !== false ? $user_token : $user_token) ;?>' + url;
                });
                //--></script>
        </div>
<?php echo $footer; ?>