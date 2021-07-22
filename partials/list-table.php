<table class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>">
    <thead>
        <tr>
            <?php $this->print_column_headers(); ?>
        </tr>
    </thead>

    <tbody id="the-list"<?php echo $singular ? " data-wp-lists='list:$singular'" : ''; ?>>
        <?php  $this->display_rows_or_placeholder(); ?>
    </tbody>

    <tfoot>
        <tr>
            <?php $this->print_column_headers(false); ?>
        </tr>
    </tfoot>
</table>