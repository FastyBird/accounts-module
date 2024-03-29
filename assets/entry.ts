import { App } from 'vue';

import moduleRouter from './router';
import { IAccountsModuleOptions, InstallFunction } from './types';
import { configurationKey, metaKey } from './configuration';

export function createAccountsModule(): InstallFunction {
	return {
		install(app: App, options: IAccountsModuleOptions): void {
			if (this.installed) {
				return;
			}
			this.installed = true;

			if (typeof options.router === 'undefined') {
				throw new Error('Router instance is missing in module configuration');
			}

			moduleRouter(options.router);

			app.provide(metaKey, options.meta);
			app.provide(configurationKey, options.configuration);
		},
	};
}

export * from './configuration';
export * from './components';
export * from './composables';
export * from './models';
export * from './router';

export * from './types';
