import { defineStore, Pinia, Store } from 'pinia';
import axios from 'axios';
import { Jsona } from 'jsona';
import addFormats from 'ajv-formats';
import Ajv from 'ajv/dist/2020';
import { v4 as uuid } from 'uuid';
import get from 'lodash.get';

import { IdentityDocument, AccountsModuleRoutes as RoutingKeys, ModulePrefix, IdentityState } from '@fastybird/metadata-library';

import exchangeDocumentSchema from '../../../resources/schemas/document.identity.json';

import { ApiError } from '../../errors';
import { JsonApiJsonPropertiesMapper, JsonApiModelPropertiesMapper } from '../../jsonapi';
import { useAccounts } from '../../models';
import { IAccount } from '../accounts/types';

import {
	IIdentity,
	IIdentitiesAddActionPayload,
	IIdentitiesEditActionPayload,
	IIdentityRecordFactoryPayload,
	IIdentityResponseModel,
	IIdentityResponseJson,
	IIdentitiesResponseJson,
	IIdentitiesState,
	IIdentitiesGetActionPayload,
	IIdentitiesFetchActionPayload,
	IIdentitiesSaveActionPayload,
	IIdentitiesRemoveActionPayload,
	IIdentitiesSocketDataActionPayload,
	IIdentitiesUnsetActionPayload,
	IIdentitiesSetActionPayload,
	IIdentitiesActions,
	IIdentitiesGetters,
} from './types';

const jsonSchemaValidator = new Ajv();
addFormats(jsonSchemaValidator);

const jsonApiFormatter = new Jsona({
	modelPropertiesMapper: new JsonApiModelPropertiesMapper(),
	jsonPropertiesMapper: new JsonApiJsonPropertiesMapper(),
});

const recordFactory = async (data: IIdentityRecordFactoryPayload): Promise<IIdentity> => {
	const accountsStore = useAccounts();

	let account = accountsStore.findById(data.accountId);

	if (account === null) {
		if (!(await accountsStore.get({ id: data.accountId }))) {
			throw new Error("Account for identity couldn't be loaded from server");
		}

		account = accountsStore.findById(data.accountId);

		if (account === null) {
			throw new Error("Account for identity couldn't be loaded from store");
		}
	}

	return {
		id: get(data, 'id', uuid().toString()),
		type: data.type,

		draft: get(data, 'draft', false),

		state: get(data, 'state', IdentityState.ACTIVE),

		uid: data.uid,
		password: get(data, 'password', undefined) as string | undefined,

		// Relations
		relationshipNames: ['account'],

		account: {
			id: account.id,
			type: account.type,
		},
	} as IIdentity;
};

export const useIdentities = defineStore<string, IIdentitiesState, IIdentitiesGetters, IIdentitiesActions>('accounts_module_identities', {
	state: (): IIdentitiesState => {
		return {
			semaphore: {
				fetching: {
					items: [],
					item: [],
				},
				creating: [],
				updating: [],
				deleting: [],
			},

			firstLoad: [],

			data: {},
		};
	},

	getters: {
		firstLoadFinished: (state: IIdentitiesState): ((accountId: IAccount['id']) => boolean) => {
			return (accountId: IAccount['id']): boolean => state.firstLoad.includes(accountId);
		},

		getting: (state: IIdentitiesState): ((id: IIdentity['id']) => boolean) => {
			return (id: IIdentity['id']): boolean => state.semaphore.fetching.item.includes(id);
		},

		fetching: (state: IIdentitiesState): ((accountId: IAccount['id'] | null) => boolean) => {
			return (accountId: IAccount['id'] | null): boolean =>
				accountId !== null ? state.semaphore.fetching.items.includes(accountId) : state.semaphore.fetching.items.length > 0;
		},

		findById: (state: IIdentitiesState): ((id: IIdentity['id']) => IIdentity | null) => {
			return (id: IIdentity['id']): IIdentity | null => {
				const identity = Object.values(state.data).find((identity) => identity.id === id);

				return identity ?? null;
			};
		},

		findForAccount: (state: IIdentitiesState): ((accountId: IAccount['id']) => IIdentity[]) => {
			return (accountId: IAccount['id']): IIdentity[] => {
				return Object.values(state.data).filter((identity) => identity.account.id === accountId);
			};
		},
	},

	actions: {
		/**
		 * Set record from via other store
		 *
		 * @param {IIdentitiesUnsetActionPayload} payload
		 */
		async set(payload: IIdentitiesSetActionPayload): Promise<IIdentity> {
			const record = await recordFactory(payload.data);

			return (this.data[record.id] = record);
		},

		/**
		 * Unset record from via other store
		 *
		 * @param {IIdentitiesUnsetActionPayload} payload
		 */
		unset(payload: IIdentitiesUnsetActionPayload): void {
			if (payload.account !== undefined) {
				Object.keys(this.data).forEach((id) => {
					if (id in this.data && this.data[id].account.id === payload.account?.id) {
						delete this.data[id];
					}
				});

				return;
			} else if (payload.id !== undefined) {
				if (payload.id in this.data) {
					delete this.data[payload.id];
				}

				return;
			}

			throw new Error('You have to provide at least account or identity id');
		},

		/**
		 * Get one record from server
		 *
		 * @param {IIdentitiesGetActionPayload} payload
		 */
		async get(payload: IIdentitiesGetActionPayload): Promise<boolean> {
			if (this.semaphore.fetching.item.includes(payload.id)) {
				return false;
			}

			this.semaphore.fetching.item.push(payload.id);

			try {
				const identityResponse = await axios.get<IIdentityResponseJson>(
					`/${ModulePrefix.ACCOUNTS}/v1/accounts/${payload.account.id}/identities/${payload.id}`
				);

				const identityResponseModel = jsonApiFormatter.deserialize(identityResponse.data) as IIdentityResponseModel;

				this.data[identityResponseModel.id] = await recordFactory({
					...identityResponseModel,
					...{ accountId: identityResponseModel.account.id },
				});
			} catch (e: any) {
				throw new ApiError('accounts-module.identities.get.failed', e, 'Fetching identity failed.');
			} finally {
				this.semaphore.fetching.item = this.semaphore.fetching.item.filter((item) => item !== payload.id);
			}

			return true;
		},

		/**
		 * Fetch all records from server
		 *
		 * @param {IIdentitiesFetchActionPayload} payload
		 */
		async fetch(payload: IIdentitiesFetchActionPayload): Promise<boolean> {
			if (this.semaphore.fetching.items.includes(payload.account.id)) {
				return false;
			}

			this.semaphore.fetching.items.push(payload.account.id);

			try {
				const identitiesResponse = await axios.get<IIdentitiesResponseJson>(`/${ModulePrefix.ACCOUNTS}/v1/accounts/${payload.account.id}/identities`);

				const identitiesResponseModel = jsonApiFormatter.deserialize(identitiesResponse.data) as IIdentityResponseModel[];

				for (const identity of identitiesResponseModel) {
					this.data[identity.id] = await recordFactory({
						...identity,
						...{ accountId: identity.account.id },
					});
				}

				this.firstLoad.push(payload.account.id);
			} catch (e: any) {
				throw new ApiError('accounts-module.identities.fetch.failed', e, 'Fetching identities failed.');
			} finally {
				this.semaphore.fetching.items = this.semaphore.fetching.items.filter((item) => item !== payload.account.id);
			}

			return true;
		},

		/**
		 * Add new record
		 *
		 * @param {IIdentitiesAddActionPayload} payload
		 */
		async add(payload: IIdentitiesAddActionPayload): Promise<IIdentity> {
			const newIdentity = await recordFactory({
				...{
					id: payload?.id,
					type: payload?.type,
					draft: payload?.draft,
					accountId: payload.account.id,
				},
				...payload.data,
			});

			this.semaphore.creating.push(newIdentity.id);

			this.data[newIdentity.id] = newIdentity;

			if (newIdentity.draft) {
				this.semaphore.creating = this.semaphore.creating.filter((item) => item !== newIdentity.id);

				return newIdentity;
			} else {
				try {
					const createdIdentity = await axios.post<IIdentityResponseJson>(
						`/${ModulePrefix.ACCOUNTS}/v1/accounts/${payload.account.id}/identities`,
						jsonApiFormatter.serialize({
							stuff: newIdentity,
						})
					);

					const createdIdentityModel = jsonApiFormatter.deserialize(createdIdentity.data) as IIdentityResponseModel;

					this.data[createdIdentityModel.id] = await recordFactory({
						...createdIdentityModel,
						...{ accountId: createdIdentityModel.account.id },
					});

					return this.data[createdIdentityModel.id];
				} catch (e: any) {
					// Entity could not be created on api, we have to remove it from database
					delete this.data[newIdentity.id];

					throw new ApiError('accounts-module.identities.create.failed', e, 'Create new identity failed.');
				} finally {
					this.semaphore.creating = this.semaphore.creating.filter((item) => item !== newIdentity.id);
				}
			}
		},

		/**
		 * Edit existing record
		 *
		 * @param {IIdentitiesEditActionPayload} payload
		 */
		async edit(payload: IIdentitiesEditActionPayload): Promise<IIdentity> {
			if (this.semaphore.updating.includes(payload.id)) {
				throw new Error('accounts-module.identities.update.inProgress');
			}

			if (!Object.keys(this.data).includes(payload.id)) {
				throw new Error('accounts-module.identities.update.failed');
			}

			this.semaphore.updating.push(payload.id);

			// Get record stored in database
			const existingRecord = this.data[payload.id];
			// Update with new values
			const updatedRecord = { ...existingRecord } as IIdentity;

			this.data[payload.id] = updatedRecord;

			if (updatedRecord.draft) {
				this.semaphore.updating = this.semaphore.updating.filter((item) => item !== payload.id);

				return this.data[payload.id];
			} else {
				try {
					const updatedIdentity = await axios.patch<IIdentityResponseJson>(
						`/${ModulePrefix.ACCOUNTS}/v1/accounts/${updatedRecord.account.id}/identities/${updatedRecord.id}`,
						jsonApiFormatter.serialize({
							stuff: updatedRecord,
						})
					);

					const updatedIdentityModel = jsonApiFormatter.deserialize(updatedIdentity.data) as IIdentityResponseModel;

					this.data[updatedIdentityModel.id] = await recordFactory({
						...updatedIdentityModel,
						...{ accountId: updatedIdentityModel.account.id },
					});

					return this.data[updatedIdentityModel.id];
				} catch (e: any) {
					const accountsStore = useAccounts();

					const account = accountsStore.findById(updatedRecord.account.id);

					if (account !== null) {
						// Updating entity on api failed, we need to refresh entity
						await this.get({ account, id: payload.id });
					}

					throw new ApiError('accounts-module.identities.update.failed', e, 'Edit identity failed.');
				} finally {
					this.semaphore.updating = this.semaphore.updating.filter((item) => item !== payload.id);
				}
			}
		},

		/**
		 * Save draft record on server
		 *
		 * @param {IIdentitiesSaveActionPayload} payload
		 */
		async save(payload: IIdentitiesSaveActionPayload): Promise<IIdentity> {
			if (this.semaphore.updating.includes(payload.id)) {
				throw new Error('accounts-module.identities.save.inProgress');
			}

			if (!Object.keys(this.data).includes(payload.id)) {
				throw new Error('accounts-module.identities.save.failed');
			}

			this.semaphore.updating.push(payload.id);

			const recordToSave = this.data[payload.id];

			try {
				const savedIdentity = await axios.post<IIdentityResponseJson>(
					`/${ModulePrefix.ACCOUNTS}/v1/accounts/${recordToSave.account.id}/identities`,
					jsonApiFormatter.serialize({
						stuff: recordToSave,
					})
				);

				const savedIdentityModel = jsonApiFormatter.deserialize(savedIdentity.data) as IIdentityResponseModel;

				this.data[savedIdentityModel.id] = await recordFactory({
					...savedIdentityModel,
					...{ accountId: savedIdentityModel.account.id },
				});

				return this.data[savedIdentityModel.id];
			} catch (e: any) {
				throw new ApiError('accounts-module.identities.save.failed', e, 'Save draft identity failed.');
			} finally {
				this.semaphore.updating = this.semaphore.updating.filter((item) => item !== payload.id);
			}
		},

		/**
		 * Remove existing record from store and server
		 *
		 * @param {IIdentitiesRemoveActionPayload} payload
		 */
		async remove(payload: IIdentitiesRemoveActionPayload): Promise<boolean> {
			if (this.semaphore.deleting.includes(payload.id)) {
				throw new Error('accounts-module.identities.delete.inProgress');
			}

			if (!Object.keys(this.data).includes(payload.id)) {
				throw new Error('accounts-module.identities.delete.failed');
			}

			this.semaphore.deleting.push(payload.id);

			const recordToDelete = this.data[payload.id];

			delete this.data[payload.id];

			if (recordToDelete.draft) {
				this.semaphore.deleting = this.semaphore.deleting.filter((item) => item !== payload.id);
			} else {
				try {
					await axios.delete(`/${ModulePrefix.ACCOUNTS}/v1/accounts/${recordToDelete.account.id}/identities/${recordToDelete.id}`);
				} catch (e: any) {
					const accountsStore = useAccounts();

					const account = accountsStore.findById(recordToDelete.account.id);

					if (account !== null) {
						// Deleting entity on api failed, we need to refresh entity
						await this.get({ account, id: payload.id });
					}

					throw new ApiError('accounts-module.identities.delete.failed', e, 'Delete identity failed.');
				} finally {
					this.semaphore.deleting = this.semaphore.deleting.filter((item) => item !== payload.id);
				}
			}

			return true;
		},

		/**
		 * Receive data from sockets
		 *
		 * @param {IIdentitiesSocketDataActionPayload} payload
		 */
		async socketData(payload: IIdentitiesSocketDataActionPayload): Promise<boolean> {
			if (
				![
					RoutingKeys.IDENTITY_DOCUMENT_REPORTED,
					RoutingKeys.IDENTITY_DOCUMENT_CREATED,
					RoutingKeys.IDENTITY_DOCUMENT_UPDATED,
					RoutingKeys.IDENTITY_DOCUMENT_DELETED,
				].includes(payload.routingKey as RoutingKeys)
			) {
				return false;
			}

			const body: IdentityDocument = JSON.parse(payload.data);

			const isValid = jsonSchemaValidator.compile<IdentityDocument>(exchangeDocumentSchema);

			try {
				if (!isValid(body)) {
					return false;
				}
			} catch {
				return false;
			}

			if (
				!Object.keys(this.data).includes(body.id) &&
				(payload.routingKey === RoutingKeys.IDENTITY_DOCUMENT_UPDATED || payload.routingKey === RoutingKeys.IDENTITY_DOCUMENT_DELETED)
			) {
				throw new Error('accounts-module.identities.update.failed');
			}

			if (payload.routingKey === RoutingKeys.IDENTITY_DOCUMENT_DELETED) {
				delete this.data[body.id];
			} else {
				if (payload.routingKey === RoutingKeys.IDENTITY_DOCUMENT_UPDATED && this.semaphore.updating.includes(body.id)) {
					return true;
				}

				const recordData = await recordFactory({
					id: body.id,
					type: {
						source: body.source,
						entity: 'identity',
					},
					state: body.state,
					uid: body.uid,
					accountId: body.account,
				});

				if (body.id in this.data) {
					this.data[body.id] = { ...this.data[body.id], ...recordData };
				} else {
					this.data[body.id] = recordData;
				}
			}

			return true;
		},
	},
});

export const registerIdentitiesStore = (pinia: Pinia): Store => {
	return useIdentities(pinia);
};
