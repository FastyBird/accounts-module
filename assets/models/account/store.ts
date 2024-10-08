import { defineStore, Pinia, Store } from 'pinia';
import axios from 'axios';
import { Jsona } from 'jsona';

import { ModulePrefix, ModuleSource } from '@fastybird/metadata-library';

import { ApiError } from '../../errors';
import { JsonApiJsonPropertiesMapper, JsonApiModelPropertiesMapper } from '../../jsonapi';
import { useAccounts, useEmails, useIdentities, useSession } from '../../models';
import {
	IAccount,
	IAccountActions,
	IAccountGetters,
	IAccountResponseJson,
	IAccountResponseModel,
	IEmail,
	IEmailResponseJson,
	IEmailResponseModel,
	IIdentityResponseJson,
} from '../../models/types';

import {
	IAccountState,
	IAccountEditActionPayload,
	IAccountAddEmailActionPayload,
	IAccountEditEmailActionPayload,
	IAccountEditIdentityActionPayload,
	IAccountRegisterActionPayload,
	IAccountRequestResetActionPayload,
} from './types';

const jsonApiFormatter = new Jsona({
	modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
	jsonPropertiesMapper: new JsonApiJsonPropertiesMapper(),
});

export const useAccount = defineStore<string, IAccountState, IAccountGetters, IAccountActions>('accounts_module_account', {
	state: (): IAccountState => {
		return {
			semaphore: {
				creating: false,
				updating: false,
			},
			loaded: false,
		};
	},

	getters: {
		emails: (): (() => IEmail[]) => {
			return (): IEmail[] => {
				const sessionStore = useSession();

				if (sessionStore.account() === null) {
					return [];
				}

				const emailsStore = useEmails();

				return emailsStore.findForAccount(sessionStore.account()!.id);
			};
		},
	},

	actions: {
		/**
		 * Edit existing record
		 *
		 * @param {IAccountEditActionPayload} payload
		 */
		async edit(payload: IAccountEditActionPayload): Promise<boolean> {
			if (this.semaphore.updating) {
				throw new Error('accounts-module.account.update.inProgress');
			}

			const sessionStore = useSession();

			const account = sessionStore.account();

			if (account === null) {
				throw new Error('accounts-module.account.update.failed');
			}

			const accountsStore = useAccounts();

			this.semaphore.updating = true;

			// Update with new values
			const updatedRecord = { ...account, ...payload.data } as IAccount;

			try {
				const updatedAccount = await axios.patch<IAccountResponseJson>(
					`/${ModulePrefix.ACCOUNTS}/v1/me?include=emails,identities,roles`,
					jsonApiFormatter.serialize({
						stuff: updatedRecord,
					})
				);

				const updatedAccountModel = jsonApiFormatter.deserialize(updatedAccount.data) as IAccountResponseModel;

				await accountsStore.set({ data: updatedAccountModel });
			} catch (e: any) {
				// Updating record on api failed, we need to refresh record
				await accountsStore.get({ id: account.id });

				throw new ApiError('accounts-module.account.update.failed', e, 'Edit account failed.');
			} finally {
				this.semaphore.updating = false;
			}

			return true;
		},

		/**
		 * Add new email under account
		 *
		 * @param {IAccountAddEmailActionPayload} payload
		 */
		async addEmail(payload: IAccountAddEmailActionPayload): Promise<IEmail> {
			if (this.semaphore.creating) {
				throw new Error('accounts-module.account.create.inProgress');
			}

			const sessionStore = useSession();

			const account = sessionStore.account();

			if (account === null) {
				throw new Error('accounts-module.account.update.failed');
			}

			this.semaphore.creating = true;

			const emailsStore = useEmails();

			const newEmail = await emailsStore.set({
				data: {
					...payload.data,
					...{
						type: {
							source: ModuleSource.ACCOUNTS,
							entity: 'email',
						},
						accountId: account.id,
					},
				},
			});

			if (newEmail.draft) {
				this.semaphore.creating = false;

				return newEmail;
			} else {
				try {
					const createdEmail = await axios.post<IEmailResponseJson>(
						`/${ModulePrefix.ACCOUNTS}/v1/me/emails`,
						jsonApiFormatter.serialize({
							stuff: newEmail,
						})
					);

					const createdEmailModel = jsonApiFormatter.deserialize(createdEmail.data) as IEmailResponseModel;

					return await emailsStore.set({
						data: {
							...createdEmailModel,
							...{
								accountId: createdEmailModel.account.id,
							},
						},
					});
				} catch (e: any) {
					// Entity could not be created on api, we have to remove it from database
					emailsStore.unset({
						id: newEmail.id,
					});

					throw new ApiError('accounts-module.account.create.failed', e, 'Create new email failed.');
				} finally {
					this.semaphore.creating = false;
				}
			}
		},

		/**
		 * Edit existing email under account
		 *
		 * @param {IAccountEditEmailActionPayload} payload
		 */
		async editEmail(payload: IAccountEditEmailActionPayload): Promise<IEmail> {
			if (this.semaphore.updating) {
				throw new Error('accounts-module.account.update.inProgress');
			}

			const emailsStore = useEmails();

			const email = emailsStore.findByAddress(payload.id);

			if (email === null) {
				throw new Error('accounts-module.account.update.failed');
			}

			this.semaphore.updating = true;

			// Update with new values
			const updatedRecord = { ...email, ...payload.data } as IEmail;

			if (updatedRecord.draft) {
				this.semaphore.updating = false;

				return await emailsStore.set({
					data: {
						...updatedRecord,
						...{
							accountId: email.account.id,
						},
					},
				});
			} else {
				try {
					const updatedEmail = await axios.patch<IEmailResponseJson>(
						`/${ModulePrefix.ACCOUNTS}/v1/me/emails/${updatedRecord.id}`,
						jsonApiFormatter.serialize({
							stuff: updatedRecord,
						})
					);

					const updatedEmailModel = jsonApiFormatter.deserialize(updatedEmail.data) as IEmailResponseModel;

					return await emailsStore.set({
						data: {
							...updatedEmailModel,
							...{
								accountId: email.account.id,
							},
						},
					});
				} catch (e: any) {
					const accountsStore = useAccounts();

					const account = accountsStore.findById(updatedRecord.account.id);

					if (account !== null) {
						// Updating entity on api failed, we need to refresh entity
						await emailsStore.get({ account, id: payload.id });
					}

					throw new ApiError('accounts-module.account.update.failed', e, 'Edit email failed.');
				} finally {
					this.semaphore.updating = false;
				}
			}
		},

		/**
		 * Edit existing identity under account
		 *
		 * @param {IAccountEditIdentityActionPayload} payload
		 */
		async editIdentity(payload: IAccountEditIdentityActionPayload): Promise<boolean> {
			if (this.semaphore.updating) {
				throw new Error('accounts-module.account.update.inProgress');
			}

			const identitiesStore = useIdentities();

			const identity = identitiesStore.findById(payload.id);

			if (identity === null) {
				throw new Error('accounts-module.account.update.failed');
			}

			this.semaphore.updating = true;

			try {
				await axios.patch<IIdentityResponseJson>(
					`/${ModulePrefix.ACCOUNTS}/v1/me/identities/${identity.id}`,
					jsonApiFormatter.serialize({
						stuff: {
							...identity,
							...{
								password: {
									current: payload.data.password.current,
									new: payload.data.password.new,
								},
							},
						},
					})
				);
			} catch (e: any) {
				const accountsStore = useAccounts();

				const account = accountsStore.findById(identity.account.id);

				if (account !== null) {
					// Updating entity on api failed, we need to refresh entity
					await identitiesStore.get({ account, id: payload.id });
				}

				throw new ApiError('accounts-module.account.update.failed', e, 'Edit identity failed.');
			} finally {
				this.semaphore.updating = false;
			}

			return true;
		},

		/**
		 * Request password reset process
		 *
		 * @param {IAccountRequestResetActionPayload} payload
		 */
		async requestReset(payload: IAccountRequestResetActionPayload): Promise<boolean> {
			try {
				const resetResponse = await axios.post(
					`/${ModulePrefix.ACCOUNTS}/v1/reset-identity`,
					jsonApiFormatter.serialize({
						stuff: {
							type: `${ModuleSource.ACCOUNTS}/identity`,

							uid: payload.uid,
						},
					})
				);

				return resetResponse.status >= 200 && resetResponse.status < 300;
			} catch (e: any) {
				throw new ApiError('accounts-module.account.requestReset.failed', e, 'Request identity reset failed.');
			}
		},

		/**
		 * Register new account
		 *
		 * @param {IAccountRegisterActionPayload} payload
		 */
		async register(payload: IAccountRegisterActionPayload): Promise<boolean> {
			// TODO: Implement

			try {
				await axios.post<IAccountResponseJson>(
					`/${ModulePrefix.ACCOUNTS}/v1/register`,
					jsonApiFormatter.serialize({
						stuff: {
							email: payload.emailAddress,
						},
					})
				);
			} catch (e: any) {
				throw new ApiError('accounts-module.account.register.failed', e, 'Register account failed.');
			}

			return true;
		},
	},
});

export const registerAccountStore = (pinia: Pinia): Store => {
	return useAccount(pinia);
};
