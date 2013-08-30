<?php
// no direct access
defined('_JEXEC') or die;
?>

<div id="modal-automailing" class="modal hide fade">
	<div class="modal-header">
		<button data-dismiss="modal" class="close" type="button">x</button>
		<h3><?php echo JText::_('JTOOLBAR_NEW'); ?></h3>
	</div>
	<div class="modal-body"></div>
</div>

<form id="adminForm" action="<?php echo JRoute::_('index.php?option=com_newsletter&view=automailings&form=automailings');?>" method="post" name="adminForm" >

	<?php echo JHtml::_('layout.wrapper'); ?>

	<div id="automailing-list">
		<fieldset>
			<div class="legend"><?php echo JText::_('COM_NEWSLETTER_AUTOMAILINGS'); ?></div>

			<div>
				<div class="pull-left btn-group">
					<select name="filter_published" class="input-medium" onchange="this.form.submit()">
						<option value="">- <?php echo JText::_('COM_NEWSLETTER_SELECT_STATE');?> -</option>
						<?php echo JHtml::_('select.options', JHtml::_('multigrid.trashedOptions'), 'value', 'text', $this->get('state')->get('filter.published'), true);?>
					</select>
				</div>
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="automailing_filter_search" class="migur-search" value="<?php echo $this->escape($this->automailings->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_NEWSLETTER_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_NEWSLETTER_FILTER_SEARCH_DESC'); ?>"/>
				</div>
				<div class="btn-group pull-left">
					<button type="submit" class="btn tip migur-search-submit" data-original-title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
					<button rel="tooltip" onclick="document.id('automailing_filter_search').value='';this.form.submit(); return false;" type="button" class="btn tip btn-danger" data-original-title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
				</div>
			</div>

			<table class="automailingslist adminlist  table table-striped" width="100%">
					<thead>
							<tr>
									<th width="1%">
											<input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" />
									</th>
									<th class="left">
											<?php echo JHtml::_('multigrid.sort', 'COM_NEWSLETTER_AUTOMAILING', 'a.title', $this->automailings->listDirn, $this->automailings->listOrder, null, null, 'adminForm'); ?>
									</th>
							</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="2">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
					<?php
							foreach ($this->automailings->items as $i => $item) {
						?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="center">
									<?php
										$idx = $item->automailing_id;
										echo JHtml::_('multigrid.id', $i, $idx, false, 'cid', 'adminForm');
									?>
								</td>
								<td>
									<a href="<?php echo JRoute::_('index.php?option=com_newsletter&task=automailing.edit&automailing_id='.(int) $item->automailing_id); ?>">
										<?php echo $this->escape($item->automailing_name); ?>
									</a>
									<?php if($item->state == -2) { ?>
									&nbsp;&nbsp;&nbsp;<span class="icon-trash icon-block-16"></span>
									<?php }	?>
									<a href="#" class="search icon-search"></a>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>

					<div>
							<input type="hidden" name="task" value="" />
							<input type="hidden" name="boxchecked" value="0" />
							<input type="hidden" name="filter_order" value="<?php echo $this->automailings->listOrder; ?>" />
							<input type="hidden" name="filter_order_Dir" value="<?php echo $this->automailings->listDirn; ?>" />
							<?php echo JHtml::_('form.token'); ?>
					</div>
		</fieldset>
	</div>

	<div id="automailing-details">

		<ul class="nav nav-tabs">
			<li class="active">
				<a data-toggle="tab" href="#details"><?php echo JText::_('COM_NEWSLETTER_PREVIEW'); ?></a>
			</li>
		</ul>

		<div class="tab-content">
			<div id="details" class="tab-pane active">
				<iframe id="preview-container" frameBorder="0"></iframe>
			</div>
		</div>

	</div>

	<?php echo JHtml::_('layout.wrapperEnd'); ?>

</form>
