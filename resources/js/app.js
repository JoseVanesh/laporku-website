import { initAuthForms } from './auth';
import { initDashboardMap, initLocationPicker, initPointMap } from './map';
import { initLanding } from './landing';

initAuthForms();

if (document.querySelector('[data-landing-nav]')) {
    initLanding();
}

const dashboardMap = document.getElementById('dashboard-map');

if (dashboardMap) {
    window.laporkuDashboardMap = initDashboardMap(dashboardMap);
}

const locationPicker = document.getElementById('location-picker');

if (locationPicker) {
    initLocationPicker(locationPicker);
}

const pointMap = document.getElementById('point-map');

if (pointMap) {
    initPointMap(pointMap);
}
