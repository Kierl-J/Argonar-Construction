// BOQ Form JavaScript (add/remove rows, calculate totals)

$(document).ready(function() {

    // Add row
    $('#addRow').on('click', function() {
        var rowCount = $('#itemsBody tr').length + 1;
        var newRow =
        '<tr class="item-row">' +
            '<td class="row-number">' + rowCount + '</td>' +
            '<td><input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="Item description" required></td>' +
            '<td>' +
                '<select name="item_unit[]" class="form-select form-select-sm">' +
                    '<option value="lot">lot</option><option value="pc">pc</option><option value="set">set</option>' +
                    '<option value="cu.m">cu.m</option><option value="sq.m">sq.m</option><option value="lin.m">lin.m</option>' +
                    '<option value="kg">kg</option><option value="bag">bag</option><option value="sheet">sheet</option>' +
                    '<option value="length">length</option><option value="day">day</option><option value="trip">trip</option>' +
                '</select>' +
            '</td>' +
            '<td><input type="number" name="item_quantity[]" class="form-control form-control-sm qty-input" step="0.001" min="0" value="0"></td>' +
            '<td><input type="number" name="item_unit_cost[]" class="form-control form-control-sm cost-input" step="0.01" min="0" value="0"></td>' +
            '<td><input type="text" class="form-control form-control-sm amount-display" readonly value="0.00"></td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>' +
        '</tr>';
        $('#itemsBody').append(newRow);
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        if ($('#itemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            updateRowNumbers();
            calculateTotals();
        }
    });

    // Calculate on input change
    $(document).on('input', '.qty-input, .cost-input', function() {
        var row = $(this).closest('tr');
        var qty = parseFloat(row.find('.qty-input').val()) || 0;
        var cost = parseFloat(row.find('.cost-input').val()) || 0;
        var amount = qty * cost;
        row.find('.amount-display').val(amount.toFixed(2));
        calculateTotals();
    });

    // Markup and VAT change
    $('#markupPct, #vatPct').on('input', calculateTotals);

    function updateRowNumbers() {
        $('#itemsBody tr').each(function(i) {
            $(this).find('.row-number').text(i + 1);
        });
    }

    function calculateTotals() {
        var subtotal = 0;
        $('#itemsBody tr').each(function() {
            subtotal += parseFloat($(this).find('.amount-display').val()) || 0;
        });

        var markupPct = parseFloat($('#markupPct').val()) || 0;
        var vatPct = parseFloat($('#vatPct').val()) || 0;

        var markup = subtotal * (markupPct / 100);
        var afterMarkup = subtotal + markup;
        var vat = afterMarkup * (vatPct / 100);
        var grandTotal = afterMarkup + vat;

        $('#subtotal').text(formatCurrency(subtotal));
        $('#markupAmount').text(formatCurrency(markup));
        $('#vatAmount').text(formatCurrency(vat));
        $('#grandTotal').text(formatCurrency(grandTotal));
    }

    // Initial calculation
    calculateTotals();
});
