<?php

// Create shortcut
$urls = json_decode($this->item->urls);

// Create shortcuts to some parameters.
$params		= $this->item->params;

?>

<?php if ($urls && (!empty($urls->urla) || !empty($urls->urlb) || !empty($urls->urlc))) : ?>
<ul class="content-links">
	<?php
		$urlarray = array(
		array($urls->urla, $urls->urlatext, $urls->targeta, 'a'),
		array($urls->urlb, $urls->urlbtext, $urls->targetb, 'b'),
		array($urls->urlc, $urls->urlctext, $urls->targetc, 'c')
		);
		foreach($urlarray as $url) :
			$link = $url[0];
			$label = $url[1];
			$target = $url[2];
			$id = $url[3];

			if( ! $link) :
				continue;
			endif;

			// If no label is present, take the link
			$label = ($label) ? $label : $link;

			// If no target is present, use the default
			$target = $target ? $target : $params->get('target'.$id);
			?>
		<li class="content-links-<?php echo $id; ?>">
			<?php
				// Compute the correct link

				switch ($target)
				{
					case 1:
						// open in a new window
						echo '<a href="'. $link .'" target="_blank"  rel="nofollow">'.
							$label .'</a>';
						break;

					case 2:
						// open in a popup window
						$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600';
						echo "<a href=\"$link\" onclick=\"window.open(this.href, 'targetWindow', '".$attribs."'); return false;\">".
							$label.'</a>';
						break;
					case 3:
						// open in a modal window
                       JHtml::_('behavior.modal', 'a.modal');
                       echo '<a class="modal" href="'.htmlspecialchars($link).'"  rel="{handler: \'iframe\', size: {x:600, y:600}}">'.
                            htmlspecialchars($label) . ' </a>';
						break;

					default:
						// open in parent window
						echo '<a href="'.  $link . '" rel="nofollow">'.
							$label . ' </a>';
						break;
				}
			?>
			</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>