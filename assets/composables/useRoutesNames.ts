import { IRoutes, UseRouteNames } from '../types';

export function useRoutesNames(): UseRouteNames {
	const routeNames: IRoutes = {
		root: 'accounts_module-root',
		signIn: 'accounts_module-sign_in',
		signUp: 'accounts_module-sign_up',
		signOut: 'accounts_module-sign_out',
		resetPassword: 'accounts_module-reset_password',
		account: 'accounts_module-account',
		accountProfile: 'accounts_module-account_profile',
		accountPassword: 'accounts_module-account_password',
	};

	return {
		routeNames,
	};
}
