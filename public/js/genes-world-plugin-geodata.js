'use strict';

(function( $ ) {

var geojson;
var info;
var map;
var legend;
var grid;

let statTypes, activeTotalsType, activeTotalsTypeAttr, activeTotalsValueType;

const $mapFeatureDetailsInfo = $('.map-feature-details__info');
const $exitRegionViewBtn = $('.js-exit-region-view-btn');
const $gridRowInfo = $('.grid-row-info');

// GEODATA

function highlightFeature(e) {
    let layer = e.target;

    layer.setStyle({
        weight: 5,
        color: '#666',
        dashArray: '',
        fillOpacity: 0.7
    });

    layer.bringToFront();
    info.update(layer.feature.properties);
}

function resetHighlight(e) {
    let layer = e.target;
    geojson.resetStyle(layer);
    if (layer.active) {
        layer.setStyle({
            dashArray: '',
            fillColor: 'lime',
            fillOpacity: 0.5
        });
    }
    info.update();
}

function resetRegionView() {
    geojson.eachLayer((itemLayer) => {
        geojson.resetStyle(itemLayer);
        itemLayer.active = false;
    });

    $mapFeatureDetailsInfo.html('');
    $('.grid-data-wrapper').toggle(true);
    $exitRegionViewBtn.toggle(false);
    $gridRowInfo.toggle(false);
    
    grid.updateConfig({
        data: window.genes_world.gridData,
        pagination: {
            limit: 15,
            summary: false
        }
    }).forceRender();
    
}

function onFeatueClick(e) {
    let layer = e.target;
    let props = layer.feature.properties;
    let layerHasData = !!(props.genes && props.genes[activeTotalsType]);
    // zoomToFeature(e);
    geojson.eachLayer((itemLayer) => {
        geojson.resetStyle(itemLayer);
        itemLayer.active = false;
    });
    layer.active = true;

    $mapFeatureDetailsInfo.html('<strong>'+props.NAME+'</strong>');
    $('.grid-data-wrapper').toggle(layerHasData);
    $exitRegionViewBtn.toggle(layerHasData);
    $gridRowInfo.toggle(false);
    
    if (!layerHasData) return;
    
    let countryTitle = props.genes[activeTotalsType].regionTitle;
    let countryTitleMapped = Object.keys(window.genes_world.country_mapping).find(key => window.genes_world.country_mapping[key] === countryTitle);
    grid.updateConfig({
        data: window.genes_world.gridData.filter(row => row[1] === (countryTitleMapped || countryTitle)),
        pagination: false
    }).forceRender();
}

function onEachFeature(feature, layer) {
    layer.on({
        mouseover: highlightFeature,
        mouseout: resetHighlight,
        click: onFeatueClick
    });
}

// https://colorbrewer2.org/#type=sequential&scheme=YlGn&n=9
const palette = {
    'orange': ['#FFEDA0', '#FED976', '#FEB24C', '#FD8D3C', '#FC4E2A', '#E31A1C', '#BD0026', '#800026', '#56001a'],
    'green': ['#ffffe5', '#f7fcb9', '#d9f0a3', '#addd8e', '#78c679', '#41ab5d', '#238443', '#006837', '#004529']
};
let colorSteps = [];

function updateDynamicColorSteps(genes_world) {
    colorSteps = [];
    let paletteType = 'green';
    if (typeof activeTotalsValueType !== 'object') {
        let min = genes_world[activeTotalsType]['stats'][activeTotalsTypeAttr].minValue;
        let max = genes_world[activeTotalsType]['stats'][activeTotalsTypeAttr].maxValue;
        const stepSize = Math.ceil((max - min) / (palette[paletteType].length)); // Use ceil to round up to the nearest integer

        // Generate the color steps
        colorSteps = palette[paletteType].map((color, index) => {
            let value = Math.round(min + index * stepSize);
            return [value, color];
        });
    } else {
        colorSteps = Object.keys(activeTotalsValueType).map(key => [key, activeTotalsValueType[key]]);
    }
    colorSteps.unshift([0, '#ccc']); // NO DATA COLOR
}

function getColor(d) {
    // edge cases
    if (d === 0) return colorSteps[0][1];

    // rest of values
    if (typeof activeTotalsValueType !== 'object') {
        for (let i = colorSteps.length - 1; i >= 0; i--) {
            if (d >= colorSteps[i][0]) return colorSteps[i][1];
        }
    } else {
        return colorSteps[d][1];
    }
}

function style(feature) {
    let genesData = feature.properties.genes;
    return {
        fillColor: getColor((genesData && genesData[activeTotalsType] && genesData[activeTotalsType][activeTotalsTypeAttr]) || 0),
        weight: 2,
        opacity: 1,
        color: 'white',
        dashArray: '3',
        fillOpacity: 0.6
    };
}

function initInfo() {
    info = L.control();

    info.onAdd = function (map) {
        this._div = L.DomUtil.create('div', 'info'); // create a div with a class "info"
        this.update();
        return this._div;
    };

    // method that we will use to update the control based on feature properties passed
    info.update = function (props) {
        let msg = '<h4>Country info</h4>';
        if (props) {
            msg += '<b>' + props.NAME + '</b><br/>';
            if (props.genes && props.genes[activeTotalsType]) {
                if (typeof activeTotalsValueType !== 'object') {
                    msg += props.genes[activeTotalsType][activeTotalsTypeAttr] + ' ' + activeTotalsValueType;
                } else {
                    if (activeTotalsTypeAttr === 'max_has_features') {
                        msg += props.genes[activeTotalsType][activeTotalsTypeAttr] + ' features present<br>(reads / variants / summary)';
                    }
                    if (activeTotalsTypeAttr === 'max_centralized_project') {
                        msg += 'National genome project:</br>' + Object.keys(activeTotalsValueType)[props.genes[activeTotalsType][activeTotalsTypeAttr] - 1];
                    }
                }
            } else msg += '-';
        } else msg += 'Hover over a country';
        this._div.innerHTML = msg;  
    };

    info.addTo(map);
}

function updateLegend(div) {
    if (!div) div = legend.getContainer(); // Get the existing legend container

    // Create new legend content
    div.innerHTML = '<i style="background:' + colorSteps[0][1] + '"></i> ' + colorSteps[0][0] + '<br>';

    for (var i = 1; i < colorSteps.length; i++) {
        if (typeof activeTotalsValueType !== 'object') {
            div.innerHTML += 
                '<i style="background:' + colorSteps[i][1] + '"></i> ' +
                colorSteps[i][0] + (colorSteps[i + 1] ? '&ndash;' + (colorSteps[i + 1][0] - 1) + activeTotalsValueType + '<br>' : '+' + activeTotalsValueType);
        } else {
            div.innerHTML += 
                '<i style="background:' + colorSteps[i][1] + '"></i> ' + Object.keys(activeTotalsValueType)[i-1] + '<br>';
        }
    }
}

function initLegend() {
    legend = L.control({position: 'bottomright'});

    legend.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'info legend');
        updateLegend(div);
        return div;
    };

    legend.addTo(map);
}

function initMap(data) {
	map = L.map('map', {
        renderer: L.canvas(),
        scrollWheelZoom: false
    }).setView([40.505, 31], 2);

	L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		minZoom: 2,
		maxZoom: 4,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map);

	geojson = L.geoJson(data, {
        style: style,
        onEachFeature: onEachFeature
    }).addTo(map);
}

function fetchGeoJSON() {
	fetch('/wp-content/plugins/genes-world-plugin/public/js/world.geojson')
		.then(response => response.json())
		.then(data => {
			// map areas with necesssary data (Totals etc)
            statTypes = window.genes_world.statTypes;
            [activeTotalsType, activeTotalsTypeAttr, activeTotalsValueType] = $('.js-form-geodata-type select').val().split('|');

            // DEBUG CODE TO IDENTIFY NOT MATCHED COUNTRIES
            // var countries = []; 
            // var notFoundCountries = [];
            // data.features.forEach( feature => { 
            //     if (!countries.includes(feature.properties.NAME)) countries.push(feature.properties.NAME);
            // });

            // window.genes_world[statTypes[0]].data.forEach( item => { 
            //     if (!countries.includes(item.regionTitle)) notFoundCountries.push(item.regionTitle);
            // });

            // console.log(countries, notFoundCountries);

			data.features.forEach( feature => {
                Object.keys(statTypes).forEach( statType => {
                    let matchedFeature = window.genes_world[statType].data.find(obj => obj.regionTitle === feature.properties.NAME);
                    if (matchedFeature) {
                        if (!feature.properties.genes) feature.properties.genes = [];
                        feature.properties.genes[statType] = {
                            'regionTitle': matchedFeature['regionTitle']
                        };
                        statTypes[statType].forEach( attr => feature.properties.genes[statType][attr] = matchedFeature[attr] );
                    }
                });
			});
			console.info('Map JSON loaded.', data);

            initGrid();
            updateDynamicColorSteps(window.genes_world);
			initGeodata(data);
		})
		.catch(error => {
			console.error('Error:', error);
		});
}

function initLayersInteraction() {
    $('.js-form-geodata-type').on('change', (e) => {
        [activeTotalsType, activeTotalsTypeAttr, activeTotalsValueType] = $(e.target).val().split('|');
        try {
            if (activeTotalsValueType.indexOf('{') === 0) activeTotalsValueType = JSON.parse(activeTotalsValueType);
        } catch (e) {
            console.error('Invalid JSON in activeTotalsValueType:', e);
            activeTotalsValueType = {};
        }

        updateDynamicColorSteps(window.genes_world);
        updateLegend();

        // Iterate over each layer and update style and events based on new properties
        geojson.eachLayer(layer => {
            // Update style based on new property
            layer.setStyle(style(layer.feature));
        });
    });

    $('.js-chart-type-link').on('click', (e) => {
        e.preventDefault();
        let $this = $(e.target);
        let $chart = $(`.js-chart-type-chart[data-chart-id="${$this.data('chart-id')}"]`);
        $('.js-chart-type-link').removeClass('is-active bu-is-active');
        $this.addClass('is-active bu-is-active');
        $chart.removeClass('is-hidden').siblings().addClass('is-hidden');
        $('.chart-notice').toggleClass('is-hidden', !$chart.find('figure').hasClass('is-disabled'));
    });

    $('.js-fullscreen-btn').on('click', (e) => {
        $(e.target).closest('.entry-content').toggleClass('is-layout-fullscreen');
        let activeChartEl = $('figure:visible').data('chartRef');
        if (activeChartEl) activeChartEl.resize();
        map.invalidateSize();
    });  
    
    $exitRegionViewBtn.on('click', resetRegionView);
}

function initGeodata(data) {
	initMap(data);
    initInfo();
    initLegend();
    initLayersInteraction();
}

function transformLinksToTags(str) {
    const urlPattern = /(https?:\/\/[^\s]+)/g;
    return str.replace(urlPattern, '<a href="$1" target="_blank">$1</a>');
}

function initGrid() {
    function formatProjectType(val) {
        return val === '2' ? 'Centralized' : (val === '3' ? 'Regional' : (val === '1' ? 'International' : '<span class="c-dimmed">n/a</span>'));
    }
    function formatBoolean(val) {
        return val === '1' ? '<span class="c-green">✔</span>' : (val === '0' ? '✘' : '<span class="c-dimmed">n/a</span>');
    }
    function getFormatterBoolean(column) {
        return {
            name: window.genes_world.gridColumns[column],
            formatter: (cell) => gridjs.html(formatBoolean(cell))
        }
    }

    window.genes_world.gridColumns[8] = getFormatterBoolean(8);
    window.genes_world.gridColumns[9] = getFormatterBoolean(9);
    window.genes_world.gridColumns[10] = getFormatterBoolean(10);
    window.genes_world.gridColumns[11] = {
        name: window.genes_world.gridColumns[11],
        formatter: (cell) => gridjs.html(formatProjectType(cell))
    };
    
    grid = new gridjs.Grid({
        columns: window.genes_world.gridColumns,
        data: window.genes_world.gridData,
        sort: true,
        style: { 
            table: { 
              'white-space': 'nowrap'
            }
          },
        autoWidth: window.innerWidth < 1024,
        search: true,
        pagination: {
            limit: 15,
            summary: false
        }
      }).render(document.getElementById('grid-data-wrapper'));

    grid.on('rowClick', (e, data) => {
        $(e.currentTarget).addClass('active').siblings().removeClass('active');
        let $valuesElements = $gridRowInfo.find('dd');

        $valuesElements.each((i, el) => {
            let val = data._cells[i].data;
            if ([8,9,10].includes(i)) {
                el.innerHTML = formatBoolean(val);
            } else if (i === 11) {
                el.innerHTML = formatProjectType(val);
            } else {
                el.innerHTML =  (val && (val.indexOf('http') >= 0)) ? transformLinksToTags(val) : val;
            }
        });

        $gridRowInfo.toggle(true);
    });
}


$(function() {
    fetchGeoJSON();
});
	
})( jQuery );