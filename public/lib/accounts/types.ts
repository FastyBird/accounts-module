import { AccountState } from '@fastybird/modules-metadata'

import {
  TJsonApiBody,
  TJsonApiData,
  TJsonApiRelation,
  TJsonApiRelationships,
  TJsonApiRelationshipData,
} from 'jsona/lib/JsonaTypes'

import {
  EmailEntityTypes,
  EmailInterface,
  EmailDataResponseInterface,
} from '@/lib/emails/types'
import {
  IdentityDataResponseInterface,
  IdentityEntityTypes,
  IdentityInterface,
} from '@/lib/identities/types'

// ENTITY TYPES
// ============

export enum AccountEntityTypes {
  USER = 'accounts-module/account',
}

// ENTITY INTERFACE
// ================

export interface AccountInterface {
  readonly id: string
  readonly type: AccountEntityTypes

  draft: boolean

  state: AccountState

  lastVisit: string | null
  registered: string | null

  firstName: string
  lastName: string
  middleName: string | null

  language: string

  weekStart: number
  timezone: string
  dateFormat: string
  timeFormat: string

  // Relations
  relationshipNames: Array<string>

  emails: Array<EmailInterface>
  identities: Array<IdentityInterface>

  // Entity transformers
  email: EmailInterface | null
}

// API RESPONSES
// =============

interface AccountDetailAttributesResponseInterface {
  first_name: string
  last_name: string
  middle_name: string | null
}

interface AccountDatetimeAttributesResponseInterface {
  timezone: string
  date_format: string
  time_format: string
}

interface AccountAttributesResponseInterface {
  state: AccountState

  // User account specific
  datetime?: AccountDatetimeAttributesResponseInterface
  details?: AccountDetailAttributesResponseInterface
  language?: string
  last_visit?: string | null
  registered?: string | null
  week_start?: number
}

interface EmailRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: EmailEntityTypes
}

interface AccountEmailsRelationshipsResponseInterface extends TJsonApiRelation {
  data: Array<EmailRelationshipResponseInterface>
}

interface IdentityRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: IdentityEntityTypes
}

interface AccountIdentitiesRelationshipsResponseInterface extends TJsonApiRelation {
  data: Array<IdentityRelationshipResponseInterface>
}

interface RoleRelationshipResponseInterface extends TJsonApiRelationshipData {
  id: string
  type: string
}

interface AccountRolesRelationshipsResponseInterface extends TJsonApiRelation {
  data: Array<RoleRelationshipResponseInterface>
}

interface UserAccountRelationshipsResponseInterface extends TJsonApiRelationships {
  emails: AccountEmailsRelationshipsResponseInterface
  identities: AccountIdentitiesRelationshipsResponseInterface
  roles: AccountRolesRelationshipsResponseInterface
}

interface MachineAccountRelationshipsResponseInterface extends TJsonApiRelationships {
  identities: AccountIdentitiesRelationshipsResponseInterface
  roles: AccountRolesRelationshipsResponseInterface
}

interface AccountDataResponseInterface extends TJsonApiData {
  id: string,
  type: AccountEntityTypes,
  attributes: AccountAttributesResponseInterface,
  relationships: UserAccountRelationshipsResponseInterface | MachineAccountRelationshipsResponseInterface,
}

export interface AccountResponseInterface extends TJsonApiBody {
  data: AccountDataResponseInterface,
  included?: Array<EmailDataResponseInterface | IdentityDataResponseInterface>
}

export interface AccountsResponseInterface extends TJsonApiBody {
  data: Array<AccountDataResponseInterface>,
}

// CREATE ENTITY INTERFACES
// ========================

export interface AccountCreateInterface {
  type: AccountEntityTypes
}

// UPDATE ENTITY INTERFACES
// ========================

export interface AccountUpdateInterface {
  firstName?: string
  lastName?: string
  middleName?: string | null
  language?: string
  weekStart?: number
  timezone?: string
  dateFormat?: string
  timeFormat?: string
}

// REGISTER NEW ACCOUNT INTERFACES
// ===============================

export interface AccountRegisterInterface {
  emailAddress: string,
  firstName: string,
  lastName: string,
  password: string,
}
