$(function () {
    const $location = $('#risk-location-select');
    const $panel = $('#reading-panel');
    if (!$location.length || !$panel.length) return;

    const show = (selector, value) => $(selector).text(value ?? 'Not available');
    const formatRain = value => value === null || value === ''
        ? 'Not available'
        : `${value} mm`;

    function loadReading() {
        const locationId = $location.val();
        if (!locationId) {
            $panel.attr('hidden', true);
            $('#reading-message').text('Choose a location to view its latest reading.');
            return;
        }

        $panel.removeAttr('hidden');
        $('#reading-message').text('Loading the latest reading…');
        $.ajax({
            url: '../../api/readings.php',
            method: 'GET',
            data: { location_id: locationId },
            dataType: 'json'
        }).done(function (response) {
            if (!response.data) {
                $('#reading-message').text('No current reading is available for this location.');
                show('#risk-level', 'No reading');
                show('#rainfall-1h', null);
                show('#rainfall-24h', null);
                show('#rainfall-72h', null);
                show('#reading-source', null);
                show('#reading-observed-at', null);
                return;
            }

            const reading = response.data;
            $('#reading-message').text('Prototype rainfall indicator; not an official warning.');
            show('#risk-level', reading.risk_level.toUpperCase());
            show('#rainfall-1h', formatRain(reading.rainfall_1h_mm));
            show('#rainfall-24h', formatRain(reading.rainfall_24h_mm));
            show('#rainfall-72h', formatRain(reading.rainfall_72h_mm));
            show('#reading-source', reading.source_name);

            const utcValue = reading.observed_at.replace(' ', 'T') + 'Z';
            const observed = new Date(utcValue);
            show('#reading-observed-at', Number.isNaN(observed.getTime())
                ? reading.observed_at
                : observed.toLocaleString());
        }).fail(function () {
            $('#reading-message').text('Could not load readings. Please try again.');
        });
    }

    $location.on('change', loadReading);
    loadReading();
});
