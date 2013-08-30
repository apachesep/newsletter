<form id="form-lists" action="<?php echo JRoute::_('index.php?option=com_newsletter&view=subscribers&form=lists', false);?>" method="post" name="listsForm">
    <fieldset>
        <div class="legend"><?php echo JText::_('COM_NEWSLETTER_LISTS'); ?></div>
    	<fieldset class="filter-bar">
			<div class="row-fluid">
				<div class="pull-right">
		            <?php echo MigurToolbar::getInstance('lists')->render(); ?>
				</div>

				<div id="lists-filter-panel-control" class="pull-left filter-panel-control" data-role="ctrl-container">
				</div>

			</div>
			<br/>

			<div id="lists-filter-panel" class="row-fluid filter-panel"  data-role="panel-container">

				<div class="filter-panel-inner" data-role="panel-container-inner">

					<div class="pull-left btn-group">
					<!--<label><?php echo JText::_('COM_NEWSLETTER_STATE'); ?></label>-->
						<select name="filter_published" class="input-medium" onchange="this.form.submit(); return false;">
								<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
								<?php echo JHtml::_('select.options', JHtml::_('multigrid.enabledOptions'), 'value', 'text', $this->lists->state->get('filter.published'), true);?>
						</select>
					</div>
					<div class="filter-search btn-group pull-left">
						<input type="text" name="filter_search" id="lists_filter_search" class="migur-search" value="<?php echo $this->escape($this->lists->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_NEWSLETTER_FILTER_SEARCH_DESC'); ?>" />
					</div>
					<div class="btn-group pull-left">
						<button class="btn tip filter-search-button" type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
						<button class="btn tip" type="button" onclick="document.id('lists_filter_search').value='';this.form.submit(); return false;"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
					</div>

				</div>
			</div>
	</fieldset>

	<table class="sslist adminlist  table table-striped">
		<thead>
			<tr>
				<th class="left" width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="left">
					<?php echo JHtml::_('multigrid.sort', 'COM_NEWSLETTER_LIST_NAME', 'a.name', $this->lists->listDirn, $this->lists->listOrder, null, null, 'listsForm'); ?>
				</th>
				<th class="left" width="22%">
					<?php echo JHtml::_('multigrid.sort', 'COM_NEWSLETTER_SUBSCRIBERS', 'subscribers', $this->lists->listDirn, $this->lists->listOrder, null, null, 'listsForm'); ?>
				</th>
				<th class="left" width="19%">
					<?php echo JHtml::_('multigrid.sort', 'COM_NEWSLETTER_ACTIVATED', 'a.state', $this->lists->listDirn, $this->lists->listOrder, NULL, 'desc', 'listsForm'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="4">
					<div class="pull-left">
						<?php echo $this->lists->pagination->getListFooter(); ?>
					</div>
					<div class="pull-right">
						<label for="limit" class="pull-left buttongroup-label"><?php echo JText::_('COM_NEWSLETTER_LIMIT'); ?></label>
						<?php echo $this->lists->pagination->getLimitBox(); ?>
					</div>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->lists->items as $i => $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
                                    <?php echo JHtml::_('multigrid.id', $i, $item->list_id, false, 'cid', 'listsForm'); ?>
				</td>
				<td>
				<?php
                if (NewsletterHelperAcl::actionIsAllowed('list.edit')) { ?>
					<a href="<?php echo JRoute::_("index.php?option=com_newsletter&task=list.edit&list_id=".(int) $item->list_id, false); ?>"
					>
						<?php echo $this->escape($item->name); ?>
					</a>
				<?php } else {
					echo $this->escape($item->name);
				}
				if($item->state == -2) { ?>
					&nbsp;&nbsp;&nbsp;<span class="icon-16-trash icon-block-16"></span>
				<?php }	?>

				</td>
				<td>
				<?php
					if (intval($item->subscribers) > 0) {
						echo '<a href="#" onclick="document.subscribersForm.filter_published.value=\'\';document.subscribersForm.filter_jusergroup.value=\'\';document.subscribersForm.filter_type.value=\'\';document.subscribersForm.filter_search.value=\'\';document.subscribersForm.filter_list.value=\'' . $item->list_id . '\';document.subscribersForm.submit();">' . $this->escape(intval($item->subscribers)) . '</a>';
					} else {
						echo '<span style="color:#cccccc">0</span>';
					}
				?>
				</td>
				<td class="center">
					<?php echo JHtml::_('multigrid.enabled', $item->state, $i, 'tick.png', 'publish_x.png', 'lists.', 'listsForm'); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists->listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists->listDirn; ?>" />
		<input type="hidden" name="form" value="lists" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
    </fieldset>
</form>

