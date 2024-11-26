import { startStimulusApp } from '@symfony/stimulus-bridge';
import { registerReactControllerComponents } from '@symfony/ux-react';
import { } from '@spomky-labs/pwa-bundle';

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
