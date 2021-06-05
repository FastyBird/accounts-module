import {
  AccountState,
  IdentityState,
  ModulePrefix,
} from '@fastybird/modules-metadata'

import { TJsonaModel } from 'jsona/lib/JsonaTypes'

import { AccountEntityTypes } from '@/lib/accounts/types'
import { EmailEntityTypes } from '@/lib/emails/types'
import { IdentityEntityTypes } from '@/lib/identities/types'

export interface AccountJsonModelInterface extends TJsonaModel {
  id: string,
  type: AccountEntityTypes,

  state: AccountState,

  lastVisit: string | null,
  registered: string | null,
}

export interface EmailJsonModelInterface extends TJsonaModel {
  id: string,
  type: EmailEntityTypes,
}

export interface IdentityJsonModelInterface extends TJsonaModel {
  id: string,
  type: IdentityEntityTypes,

  state: IdentityState,
}

export const ModuleApiPrefix = `/${ModulePrefix.MODULE_ACCOUNTS_PREFIX}`

// STORE
// =====

export enum SemaphoreTypes {
  FETCHING = 'fetching',
  GETTING = 'getting',
  CREATING = 'creating',
  UPDATING = 'updating',
  DELETING = 'deleting',
}
