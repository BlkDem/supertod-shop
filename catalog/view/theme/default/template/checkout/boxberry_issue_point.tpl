<label><?php if(isset($code) && $quote['code'] == $code) : ?>
        <input type="radio" name="shipping_method" value="<?php echo $quote['code'] ?>" checked="checked"/>
    <?php else: ?>
        <input type="radio" name="shipping_method" value="<?php echo $quote['code'] ?>"/>
    <?php endif; ?>
    <?php echo $quote['title'] ?> - <?php echo $quote['text'] ?>
    <?php if(isset($quote['boxberry_html'])) : ?>
        <br><?php echo $quote['boxberry_html'] ?>
    <?php endif; ?>
</label>
