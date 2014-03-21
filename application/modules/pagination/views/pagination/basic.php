<p class="pagination">

	<?php if ($first_page !== FALSE): ?>
		<a href="<?php echo HTML::chars($page->url($first_page)) ?>" rel="first"><?php echo __('第一页') ?></a>
	<?php else: ?>
		<?php echo __('第一页') ?>
	<?php endif ?>

	<?php if ($previous_page !== FALSE): ?>
		<a href="<?php echo HTML::chars($page->url($previous_page)) ?>" rel="prev"><?php echo __('上一页') ?></a>
	<?php else: ?>
		<?php echo __('上一页') ?>
	<?php endif ?>

	<?php for ($i = 1; $i <= $total_pages; $i++): ?>

		<?php if ($i == $current_page): ?>
			<strong><?php echo $i ?></strong>
		<?php else: ?>
			<a href="<?php echo HTML::chars($page->url($i)) ?>"><?php echo $i ?></a>
		<?php endif ?>

	<?php endfor ?>

	<?php if ($next_page !== FALSE): ?>
		<a href="<?php echo HTML::chars($page->url($next_page)) ?>" rel="next"><?php echo __('下一页') ?></a>
	<?php else: ?>
		<?php echo __('下一页') ?>
	<?php endif ?>

	<?php if ($last_page !== FALSE): ?>
		<a href="<?php echo HTML::chars($page->url($last_page)) ?>" rel="last"><?php echo __('最后一页') ?></a>
	<?php else: ?>
		<?php echo __('最后一页') ?>
	<?php endif ?>

</p><!-- .pagination -->