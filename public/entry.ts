// Import library
import { ModuleOrigin } from '@fastybird/modules-metadata'

import Account from '@/lib/accounts/Account'
import accounts from '@/lib/accounts'
import Email from '@/lib/emails/Email'
import emails from '@/lib/emails'
import Identity from '@/lib/identities/Identity'
import identities from '@/lib/identities'

// Import typing
import { ComponentsInterface, GlobalConfigInterface, InstallFunction } from '@/types/accounts-module'

// install function executed by VuexORM.use()
const install: InstallFunction = function installVuexOrmWamp(components: ComponentsInterface, config: GlobalConfigInterface) {
  if (typeof config.originName !== 'undefined') {
    // @ts-ignore
    components.Model.prototype.$accountsModuleOrigin = config.originName
  } else {
    // @ts-ignore
    components.Model.prototype.$accountsModuleOrigin = ModuleOrigin.MODULE_ACCOUNTS_ORIGIN
  }

  config.database.register(Account, accounts)
  config.database.register(Email, emails)
  config.database.register(Identity, identities)
}

// Create module definition for VuexORM.use()
const plugin = {
  install,
}

// Default export is library as a whole, registered via VuexORM.use()
export default plugin

// Export model classes
export {
  Account,
  Email,
  Identity,
}

// Re-export plugin typing
export * from '@/types/accounts-module'
