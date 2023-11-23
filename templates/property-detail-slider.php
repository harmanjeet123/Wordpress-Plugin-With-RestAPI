<div class="clearfix">
    <ul id="image-gallery" class="gallery list-unstyled cS-hidden">
    <?php foreach($property->images as $image): ?>
    <li data-thumb="<?php echo $image->src; ?>"> <img src="<?php echo $image->src; ?>" /></li>
    <?php endforeach; ?>
    </ul>
    <div class="popup-gallery">
    <?php foreach ($property->images as $image) : ?>
        <a href="<?php echo $image->src; ?>" title="">
            <img src="<?php echo $image->src; ?>" width="75" height="75">
        </a>
    <?php endforeach; ?>
    </div>
</div>
<style>
.lSSlideWrapper {background:#555;text-align:center;}
.popup-gallery img {display:none;}
.popup-gallery a:first-child:before {content:'';display:inline-block;width:24px;height:24px;background:url('/wp-content/plugins/inmolink-extras/themes/images/camera-white.png') 0 0 no-repeat;position:absolute;top:18px;right:25px; }
</style>