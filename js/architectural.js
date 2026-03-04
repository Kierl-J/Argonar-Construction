// Architectural Estimate - Form JavaScript (add/remove rows, calculate costs)

var CATEGORY_UNITS = {
    'masonry': 'sq.m',
    'tiling': 'sq.m',
    'painting': 'sq.m',
    'roofing': 'sq.m',
    'plastering': 'sq.m',
    'ceiling': 'sq.m',
    'doors_windows': 'pcs'
};

$(document).ready(function() {

    var categoryOptions =
        '<option value="masonry">Masonry</option>' +
        '<option value="tiling">Tiling</option>' +
        '<option value="painting">Painting</option>' +
        '<option value="roofing">Roofing</option>' +
        '<option value="plastering">Plastering</option>' +
        '<option value="ceiling">Ceiling</option>' +
        '<option value="doors_windows">Doors & Windows</option>';

    // Add row
    $('#addRow').on('click', function() {
        var rowCount = $('#itemsBody tr').length + 1;
        var newRow =
        '<tr class="item-row">' +
            '<td class="row-number">' + rowCount + '</td>' +
            '<td>' +
                '<select name="item_category[]" class="form-select form-select-sm category-input">' +
                    categoryOptions +
                '</select>' +
            '</td>' +
            '<td><input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="e.g. CHB Laying"></td>' +
            '<td><input type="number" name="item_quantity[]" class="form-control form-control-sm qty-input" step="0.001" min="0" value="0"></td>' +
            '<td><input type="text" name="item_unit[]" class="form-control form-control-sm unit-input" value="sq.m" readonly></td>' +
            '<td><input type="number" name="item_unit_cost[]" class="form-control form-control-sm unit-cost-input" step="0.01" min="0" value="0"></td>' +
            '<td><input type="text" class="form-control form-control-sm amount-display" readonly value="0.00"></td>' +
            '<td><input type="text" name="item_remarks[]" class="form-control form-control-sm" placeholder="Optional"></td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>' +
        '</tr>';
        $('#itemsBody').append(newRow);
        calculateSummary();
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        if ($('#itemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            updateRowNumbers();
            calculateSummary();
        }
    });

    // On category change — update unit field
    $(document).on('change', '.category-input', function() {
        var row = $(this).closest('tr');
        var category = $(this).val();
        var unit = CATEGORY_UNITS[category] || 'sq.m';
        row.find('.unit-input').val(unit);
        calculateSummary();
    });

    // On qty or unit cost change — recalculate
    $(document).on('input', '.qty-input, .unit-cost-input', function() {
        var row = $(this).closest('tr');
        calculateRow(row);
        calculateSummary();
    });

    // On contingency % change — recalculate summary
    $(document).on('input', '#contingencyPct', function() {
        calculateSummary();
    });

    function calculateRow(row) {
        var qty = parseFloat(row.find('.qty-input').val()) || 0;
        var unitCost = parseFloat(row.find('.unit-cost-input').val()) || 0;
        var amount = qty * unitCost;
        row.find('.amount-display').val(amount.toFixed(2));
    }

    function updateRowNumbers() {
        $('#itemsBody tr').each(function(i) {
            $(this).find('.row-number').text(i + 1);
        });
    }

    function formatPeso(val) {
        return '\u20B1' + val.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function calculateSummary() {
        var totals = {
            masonry: 0, tiling: 0, painting: 0, roofing: 0,
            plastering: 0, ceiling: 0, doors_windows: 0
        };

        $('#itemsBody tr').each(function() {
            var amount = parseFloat($(this).find('.amount-display').val()) || 0;
            var category = $(this).find('.category-input').val();
            if (totals.hasOwnProperty(category)) {
                totals[category] += amount;
            }
        });

        var subtotal = 0;
        for (var key in totals) subtotal += totals[key];

        var contingencyPct = parseFloat($('#contingencyPct').val()) || 0;
        var contingencyAmount = subtotal * (contingencyPct / 100);
        var grandTotal = subtotal + contingencyAmount;

        $('#totalMasonry').text(formatPeso(totals.masonry));
        $('#totalTiling').text(formatPeso(totals.tiling));
        $('#totalPainting').text(formatPeso(totals.painting));
        $('#totalRoofing').text(formatPeso(totals.roofing));
        $('#totalPlastering').text(formatPeso(totals.plastering));
        $('#totalCeiling').text(formatPeso(totals.ceiling));
        $('#totalDoorsWindows').text(formatPeso(totals.doors_windows));
        $('#subtotal').text(formatPeso(subtotal));
        $('#contingencyLabel').text(contingencyPct);
        $('#contingencyAmount').text(formatPeso(contingencyAmount));
        $('#grandTotal').text(formatPeso(grandTotal));
    }

    // Initial calculation on page load
    $('#itemsBody tr').each(function() {
        calculateRow($(this));
    });
    calculateSummary();
});
