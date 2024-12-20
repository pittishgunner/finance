import { startStimulusApp } from '@symfony/stimulus-bridge';
import { registerReactControllerComponents } from '@symfony/ux-react';

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.(j|t)sx?$/
));
app.debug = true;
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
registerReactControllerComponents(require.context('./react/controllers', true, /\.(j|t)sx?$/));

import zoomPlugin from 'chartjs-plugin-zoom';
import annotationPlugin from 'chartjs-plugin-annotation';
import autocolors from 'chartjs-plugin-autocolors';
import { WordCloudController, WordElement } from 'chartjs-chart-wordcloud';


document.addEventListener('chartjs:init', function (event) {
    const Chart = event.detail.Chart;
    Chart.register({zoomPlugin, annotationPlugin, autocolors, WordCloudController, WordElement });
});

