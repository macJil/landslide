$(function () {
    const $location = $('#risk-location-select');
    const $panel = $('#reading-panel');
    const $message = $('#reading-message');
    const $historyBody = $('#weather-hourly-body');
    if (!$location.length || !$panel.length || !$historyBody.length) return;

    let pendingRequest = null;
    let requestTimer = null;

    function isAvailable(value) {
        return value !== null && value !== undefined && value !== '' && Number.isFinite(Number(value));
    }

    function number(value, decimals = 1) {
        return isAvailable(value) ? Number(value).toFixed(decimals) : '—';
    }

    function timeLabel(value) {
        if (!value) return '—';
        const withZone = /(?:Z|[+-]\d{2}:?\d{2})$/i.test(value) ? value : `${value}+08:00`;
        const parsed = new Date(withZone);
        return Number.isNaN(parsed.getTime())
            ? value
            : parsed.toLocaleString('en-PH', { timeZone: 'Asia/Manila' });
    }

    function resetHistory(message) {
        $historyBody.empty().append(
            $('<tr>').append($('<td>', { colspan: 19, class: 'text-center text-muted' }).text(message))
        );
        $('#weather-hourly-summary').text(message);
    }

    function setRisk(rainfall) {
        const risk = rainfall.risk_level;
        const riskClass = {
            low: 'text-bg-success',
            normal: 'text-bg-primary',
            medium: 'text-bg-warning',
            high: 'text-bg-danger'
        }[risk] || 'text-bg-secondary';

        $('#risk-level')
            .removeClass('text-bg-secondary text-bg-success text-bg-primary text-bg-warning text-bg-danger')
            .addClass(riskClass)
            .text(risk ? risk.toUpperCase() : 'UNAVAILABLE');

        $('#rainfall-1h').text(isAvailable(rainfall.rainfall_1h_mm)
            ? `${number(rainfall.rainfall_1h_mm, 2)} mm` : 'Unavailable');
        $('#rainfall-24h').text(isAvailable(rainfall.rainfall_24h_mm)
            ? `${number(rainfall.rainfall_24h_mm, 2)} mm` : 'Unavailable');
        $('#rainfall-72h').text(isAvailable(rainfall.rainfall_72h_mm)
            ? `${number(rainfall.rainfall_72h_mm, 2)} mm` : 'Unavailable');
        $('#reading-observed-at').text(timeLabel(rainfall.observed_at));
    }

    function setCurrent(current) {
        $('#weather-temperature').text(isAvailable(current.temperature_2m)
            ? `${number(current.temperature_2m)} °C` : '—');
        $('#weather-apparent-temperature').text(isAvailable(current.apparent_temperature)
            ? `${number(current.apparent_temperature)} °C` : '—');
        $('#weather-humidity').text(isAvailable(current.relative_humidity_2m)
            ? `${number(current.relative_humidity_2m, 0)}%` : '—');

        const intervalMinutes = isAvailable(current.interval)
            ? `${Math.round(Number(current.interval) / 60)}-minute interval` : 'API interval';
        $('#weather-precipitation').text(isAvailable(current.precipitation)
            ? `${number(current.precipitation, 2)} mm (${intervalMinutes})` : '—');
        $('#weather-rain-showers').text(
            `${number(current.rain, 2)} mm rain / ${number(current.showers, 2)} mm showers`
        );
        $('#weather-wind').text(
            `${number(current.wind_speed_10m)} km/h / ${number(current.wind_gusts_10m)} km/h gusts`
        );
        $('#weather-wind-direction').text(isAvailable(current.wind_direction_10m)
            ? `${number(current.wind_direction_10m, 0)}°` : '—');
        $('#weather-cloud-cover').text(isAvailable(current.cloud_cover)
            ? `${number(current.cloud_cover, 0)}%` : '—');
        $('#weather-soil-moisture-shallow').text(isAvailable(current.soil_moisture_0_to_1cm)
            ? `${number(current.soil_moisture_0_to_1cm, 3)} m³/m³` : '—');
        $('#weather-soil-moisture-deep').text(isAvailable(current.soil_moisture_27_to_81cm)
            ? `${number(current.soil_moisture_27_to_81cm, 3)} m³/m³` : '—');
        $('#weather-code').text(isAvailable(current.weather_code)
            ? String(Math.round(Number(current.weather_code))) : '—');
        $('#weather-current-time').text(timeLabel(current.time));
    }

    function addCell($row, value) {
        $row.append($('<td>').text(value === null || value === undefined ? '—' : value));
    }

    function setHistory(rows, retrievedAt) {
        $historyBody.empty();
        if (!Array.isArray(rows) || rows.length === 0) {
            resetHistory('The provider did not return hourly readings for this location.');
            return;
        }

        rows.slice().reverse().forEach(function (reading) {
            const $row = $('<tr>');
            addCell($row, timeLabel(reading.time));
            addCell($row, number(reading.temperature_2m));
            addCell($row, number(reading.relative_humidity_2m, 0));
            addCell($row, number(reading.apparent_temperature));
            addCell($row, number(reading.precipitation, 2));
            addCell($row, number(reading.rain, 2));
            addCell($row, number(reading.showers, 2));
            addCell($row, number(reading.wind_speed_10m));
            addCell($row, number(reading.wind_direction_10m, 0));
            addCell($row, number(reading.wind_gusts_10m));
            addCell($row, number(reading.cloud_cover, 0));
            addCell($row, number(reading.pressure_msl, 1));
            addCell($row, number(reading.surface_pressure, 1));
            addCell($row, number(reading.soil_moisture_0_to_1cm, 3));
            addCell($row, number(reading.soil_moisture_1_to_3cm, 3));
            addCell($row, number(reading.soil_moisture_3_to_9cm, 3));
            addCell($row, number(reading.soil_moisture_9_to_27cm, 3));
            addCell($row, number(reading.soil_moisture_27_to_81cm, 3));
            addCell($row, isAvailable(reading.weather_code)
                ? String(Math.round(Number(reading.weather_code))) : '—');
            $historyBody.append($row);
        });

        $('#weather-hourly-summary').text(
            `${rows.length} hourly readings loaded. API refreshed ${timeLabel(retrievedAt)}.`
        );
    }

    function loadWeather() {
        const locationId = $location.val();
        if (pendingRequest) pendingRequest.abort();

        if (!locationId) {
            $panel.attr('hidden', true);
            $message.text('Choose a location to load its latest weather readings.');
            resetHistory('Choose a location above to load its hourly readings.');
            return;
        }

        $panel.attr('hidden', true);
        $message.text('Loading weather data and calculating the prototype risk level…');
        resetHistory('Loading hourly readings…');

        pendingRequest = $.ajax({
            url: '../../api/weather.php',
            method: 'POST',
            dataType: 'json',
            data: {
                location_id: locationId,
                csrf_token: $('#weather-csrf-token').val()
            }
        }).done(function (response) {
            if ($location.val() !== locationId || !response.data) return;

            const data = response.data;
            setRisk(data.rainfall);
            setCurrent(data.current);
            setHistory(data.hourly, data.retrieved_at);
            $panel.removeAttr('hidden');
            $message.text(
                `Weather readings loaded for ${data.location.location_name}` +
                `${data.location.purok_zone ? ` — ${data.location.purok_zone}` : ''}.`
            );
        }).fail(function (xhr, status) {
            if (status === 'abort' || $location.val() !== locationId) return;

            const error = xhr.responseJSON?.error;
            const messages = {
                location_coordinates_missing: 'This location needs verified latitude and longitude before the weather API can return readings.',
                location_not_found: 'The selected location is inactive or unavailable.',
                authentication_required: 'Your session has expired. Sign in again to load weather readings.',
                weather_provider_unavailable: 'The weather provider is temporarily unavailable. Try again shortly.',
                weather_history_unavailable: 'No recent hourly data was returned for this location.',
                invalid_csrf_token: 'Your session token expired. Reload the page and try again.'
            };

            $panel.attr('hidden', true);
            $message.text(messages[error] || 'Weather readings could not be loaded. Check the location coordinates and try again.');
            resetHistory('No hourly readings loaded.');
        });
    }

    $location.on('change', function () {
        clearTimeout(requestTimer);
        requestTimer = setTimeout(loadWeather, 250);
    });

    if ($location.val()) loadWeather();
});