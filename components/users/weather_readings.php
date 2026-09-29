<section class="card" aria-labelledby="weather-history-heading">
    <div class="card-header">
        <h2 class="h5 mb-0" id="weather-history-heading">Recent readings for the selected location</h2>
    </div>
    <div class="card-body">
        <p id="weather-hourly-summary" class="text-muted">Choose a location above to load up to 72 hourly readings from Open-Meteo.</p>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Time (Baguio)</th>
                        <th scope="col">Temp (°C)</th>
                        <th scope="col">Humidity (%)</th>
                        <th scope="col">Feels like (°C)</th>
                        <th scope="col">Precipitation (mm)</th>
                        <th scope="col">Rain (mm)</th>
                        <th scope="col">Showers (mm)</th>
                        <th scope="col">Wind (km/h)</th>
                        <th scope="col">Wind direction (°)</th>
                        <th scope="col">Gusts (km/h)</th>
                        <th scope="col">Cloud cover (%)</th>
                        <th scope="col">Pressure MSL (hPa)</th>
                        <th scope="col">Surface pressure (hPa)</th>
                        <th scope="col">Soil moisture, 0–1 cm (m³/m³)</th>
                        <th scope="col">Soil moisture, 1–3 cm (m³/m³)</th>
                        <th scope="col">Soil moisture, 3–9 cm (m³/m³)</th>
                        <th scope="col">Soil moisture, 9–27 cm (m³/m³)</th>
                        <th scope="col">Soil moisture, 27–81 cm (m³/m³)</th>
                        <th scope="col">WMO code</th>
                    </tr>
                </thead>
                <tbody id="weather-hourly-body">
                    <tr><td colspan="19" class="text-center text-muted">No weather readings loaded.</td></tr>
                </tbody>
            </table>
        </div>
        <p class="small text-muted mt-3 mb-0">The history contains provider hourly model values for the last 72 hours. Missing values are shown as an em dash. Open-Meteo current conditions are model-based estimates and may differ from ground measurements.</p>
    </div>
</section>
