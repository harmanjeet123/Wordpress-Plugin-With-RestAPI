<div class="popup-gallery">
    <?php foreach ($property->images as $image) : ?>
        <a href="<?php echo $image->src; ?>" title="">
            <img src="<?php echo $image->src; ?>" width="75" height="75">
        </a>
    <?php endforeach; ?>
</div>
