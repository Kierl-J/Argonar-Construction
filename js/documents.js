// Document Generator - Form JavaScript (add/remove rows, change_order amount calc)

$(document).ready(function() {

    var docType = $('table[data-doc-type]').data('doc-type') || '';

    // Build a template row from the first existing row
    function buildTemplateRow(rowNum) {
        var firstRow = $('#itemsBody tr:first');
        var newRow = '<tr class="item-row"><td class="row-number">' + rowNum + '</td>';

        firstRow.find('td').each(function(i) {
            if (i === 0) return; // skip row number
            var input = $(this).find('input, select, textarea');
            if (input.length) {
                var clone = input.clone();
                clone.val(input.is('[readonly]') ? '0.00' : '');
                if (clone.attr('type') === 'number') clone.val('0');
                if (clone.is('[required]')) clone.val('');
                newRow += '<td>' + $('<div>').append(clone).html() + '</td>';
            } else {
                newRow += '<td>' + $(this).html() + '</td>';
            }
        });

        newRow += '</tr>';
        return newRow;
    }

    // Add row
    $('#addRow').on('click', function() {
        var rowCount = $('#itemsBody tr').length + 1;
        var newRow = buildTemplateRow(rowCount);
        $('#itemsBody').append(newRow);
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        if ($('#itemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            updateRowNumbers();
        }
    });

    // Auto-calculate amount for change_order
    if (docType === 'change_order') {
        $(document).on('input', '.qty-input, .unit-cost-input', function() {
            var row = $(this).closest('tr');
            var qty = parseFloat(row.find('.qty-input').val()) || 0;
            var unitCost = parseFloat(row.find('.unit-cost-input').val()) || 0;
            var amount = qty * unitCost;
            row.find('.amount-display').val(amount.toFixed(2));
        });

        // Initial calc on page load
        $('#itemsBody tr').each(function() {
            var qty = parseFloat($(this).find('.qty-input').val()) || 0;
            var unitCost = parseFloat($(this).find('.unit-cost-input').val()) || 0;
            $(this).find('.amount-display').val((qty * unitCost).toFixed(2));
        });
    }

    function updateRowNumbers() {
        $('#itemsBody tr').each(function(i) {
            $(this).find('.row-number').text(i + 1);
        });
    }
});
