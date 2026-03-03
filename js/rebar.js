// Rebar Cutting List - Form JavaScript (add/remove rows, calculate weights)

// Philippine standard rebar weights (kg/m)
var REBAR_WEIGHTS = {
    '10mm': 0.617,
    '12mm': 0.888,
    '16mm': 1.578,
    '20mm': 2.466,
    '25mm': 3.853,
    '28mm': 4.834,
    '32mm': 6.313,
    '36mm': 7.990
};

$(document).ready(function() {

    // Add row
    $('#addRow').on('click', function() {
        var rowCount = $('#itemsBody tr').length + 1;
        var newRow =
        '<tr class="item-row">' +
            '<td class="row-number">' + rowCount + '</td>' +
            '<td>' +
                '<select name="item_bar_size[]" class="form-select form-select-sm bar-size-input">' +
                    '<option value="10mm">10mm</option>' +
                    '<option value="12mm">12mm</option>' +
                    '<option value="16mm">16mm</option>' +
                    '<option value="20mm">20mm</option>' +
                    '<option value="25mm">25mm</option>' +
                    '<option value="28mm">28mm</option>' +
                    '<option value="32mm">32mm</option>' +
                    '<option value="36mm">36mm</option>' +
                '</select>' +
            '</td>' +
            '<td><input type="number" name="item_pieces[]" class="form-control form-control-sm pieces-input" min="0" value="0"></td>' +
            '<td><input type="number" name="item_length[]" class="form-control form-control-sm length-input" step="0.001" min="0" value="0"></td>' +
            '<td><input type="text" class="form-control form-control-sm total-length-display" readonly value="0.000"></td>' +
            '<td><input type="text" class="form-control form-control-sm wpm-display" readonly value="0.617"></td>' +
            '<td><input type="text" class="form-control form-control-sm total-weight-display" readonly value="0.000"></td>' +
            '<td><input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="Optional"></td>' +
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

    // On bar size change — update weight/m and recalculate
    $(document).on('change', '.bar-size-input', function() {
        var row = $(this).closest('tr');
        var barSize = $(this).val();
        var wpm = REBAR_WEIGHTS[barSize] || 0.617;
        row.find('.wpm-display').val(wpm.toFixed(4));
        calculateRow(row);
        calculateSummary();
    });

    // On pieces or length change — recalculate
    $(document).on('input', '.pieces-input, .length-input', function() {
        var row = $(this).closest('tr');
        calculateRow(row);
        calculateSummary();
    });

    function calculateRow(row) {
        var pieces = parseInt(row.find('.pieces-input').val()) || 0;
        var lengthPc = parseFloat(row.find('.length-input').val()) || 0;
        var barSize = row.find('.bar-size-input').val();
        var wpm = REBAR_WEIGHTS[barSize] || 0.617;

        var totalLength = pieces * lengthPc;
        var totalWeight = totalLength * wpm;

        row.find('.wpm-display').val(wpm.toFixed(4));
        row.find('.total-length-display').val(totalLength.toFixed(3));
        row.find('.total-weight-display').val(totalWeight.toFixed(3));
    }

    function updateRowNumbers() {
        $('#itemsBody tr').each(function(i) {
            $(this).find('.row-number').text(i + 1);
        });
    }

    function calculateSummary() {
        var totalWeight = 0;
        var totalItems = $('#itemsBody tr').length;

        $('#itemsBody tr').each(function() {
            totalWeight += parseFloat($(this).find('.total-weight-display').val()) || 0;
        });

        $('#totalItems').text(totalItems);
        $('#totalWeight').text(totalWeight.toFixed(3) + ' kg');
    }

    // Initial calculation on page load
    $('#itemsBody tr').each(function() {
        calculateRow($(this));
    });
    calculateSummary();
});
