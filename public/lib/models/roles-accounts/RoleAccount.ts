import { Model } from '@vuex-orm/core'
import { RoleAccountInterface } from '@/lib/models/roles-accounts/types'

export default class RoleAccount extends Model implements RoleAccountInterface {
  static get entity(): string {
    return 'accounts_role_user'
  }

  static get primaryKey(): string[] {
    return ['roleId', 'accountId']
  }

  static fields () {
    return {
      roleId: this.string(null),
      accountId: this.string(null)
    }
  }

  roleId!: string
  accountId!: string
}
