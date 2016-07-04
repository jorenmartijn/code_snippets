<section class="page-gallery">
	<div class="container">
		<div class="gallery-mosaic">
		<?php if($gallery = get_field('gallery')):foreach($gallery as $row):
			/** 
			* the value of the $row_layout variable matches the keys of the $layouts variable, 
			* the values of which are css classes
			**/
			$layouts = array('row_50_2' 	=> ['gallery-col-half', 'gallery-col-half'],
							 'row_33_3' 	=> ['gallery-col-third', 'gallery-col-third', 'gallery-col-third'],
							 'row_25_4' 	=> ['gallery-col-quarter','gallery-col-quarter',
							 					'gallery-col-quarter','gallery-col-quarter'],
							 'row_60_40_2' 	=> ['gallery-col-wide', 'gallery-col-small'],
							 'row_60_40_3' 	=> ['gallery-col-large', 'gallery-col-medium', 'gallery-col-medium']);
			$row_layout = $row['row_layout']; 			 // get the row layout for this row
			$photos_in_layout = substr($row_layout, -1); // max number of photos in this layout
			/** 
			* check if there's photos in the photo_row and;
			* check if they have at least the number of photos required for the layout
			**/
			if(($photos = $row['photos_row']) && count($photos) >= $photos_in_layout):?>
				<div class="gallery-row"><?php 
				$classes = $layouts[$row_layout]; // assign the appropiate layout's classes to this loop
				$i = 0;foreach($photos as $item):if($i < $photos_in_layout):// check if the max number of photos is reached
				$class = $classes[$i]; // cycle through each class per photo ?>
					<div class="<?php echo $class;?>">
					<?php $image = $item['photo']; $link  = $item['link']; $description = $item['description'];?>
						<?php if($link): // link filled ?>
							<a href="<?php echo $link;?>" class="gallery-img">
								<img src="<?php echo $image['sizes']['large'];?>"/>
								<?php if($description): // does it have a description? ?>
									<span class="gallery-img-caption"><?php echo $description; ?></span>
								<?php endif;?>
							</a>
						<?php else: // not a link ?>
							<span class="gallery-img">
								<img src="<?php echo $image['sizes']['large'];?>"/>
								<?php if($description): // does it have a description? ?>
								<span class="gallery-img-caption"><?php echo $description; ?></span>
								<?php endif;?>
							</span>
						<?php endif;?>
					</div>
				<?php $i++; else: continue; endif;endforeach; // end the photos loop, continue with the next loop ?>
				</div>
			<?php endif; endforeach; endif; // end the gallery loop?>
		</div>
	</div>
</section>