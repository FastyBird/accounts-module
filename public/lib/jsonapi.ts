import {
  ModelPropertiesMapper,
  JsonPropertiesMapper,
} from 'jsona'
import {
  IModelPropertiesMapper,
  IJsonPropertiesMapper,
  TAnyKeyValueObject,
  TJsonaModel,
  TJsonaRelationships,
  TJsonaRelationshipBuild,
} from 'jsona/lib/JsonaTypes'
import { defineRelationGetter } from 'jsona/lib/simplePropertyMappers'
import clone from 'lodash/clone'
import get from 'lodash/get'

import Account from '@/lib/accounts/Account'
import { AccountEntityTypes } from '@/lib/accounts/types'
import { IdentityEntityTypes } from '@/lib/identities/types'
import { EmailEntityTypes } from '@/lib/emails/types'

const RELATIONSHIP_NAMES_PROP = 'relationshipNames'

export class JsonApiModelPropertiesMapper extends ModelPropertiesMapper implements IModelPropertiesMapper {
  getAttributes(model: TJsonaModel): { [index: string]: any } {
    const exceptProps = ['id', '$id', 'type', 'draft', RELATIONSHIP_NAMES_PROP]

    if (model.type === AccountEntityTypes.USER) {
      exceptProps.push('firstName')
      exceptProps.push('lastName')
      exceptProps.push('middleName')
      exceptProps.push('timezone')
      exceptProps.push('dateFormat')
      exceptProps.push('timeFormat')
    } else if (model.type === IdentityEntityTypes.USER) {
      exceptProps.push('account')
      exceptProps.push('accountId')
    } else if (model.type === EmailEntityTypes.EMAIL) {
      exceptProps.push('account')
      exceptProps.push('accountId')
    }

    if (Array.isArray(model[RELATIONSHIP_NAMES_PROP])) {
      exceptProps.push(...model[RELATIONSHIP_NAMES_PROP])
    }

    const attributes: { [index: string]: any } = {}

    Object.keys(model)
      .forEach((attrName) => {
        if (!exceptProps.includes(attrName)) {
          const kebabName = attrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`)

          attributes[kebabName] = model[attrName]
        }
      })

    if (model.type === AccountEntityTypes.USER) {
      attributes.details = {}
      attributes.datetime = {}

      if (Object.prototype.hasOwnProperty.call(model, 'firstName')) {
        attributes.details.first_name = model.firstName
      }

      if (Object.prototype.hasOwnProperty.call(model, 'lastName')) {
        attributes.details.last_name = model.lastName
      }

      if (Object.prototype.hasOwnProperty.call(model, 'middleName')) {
        attributes.details.middle_name = model.middleName
      }

      if (Object.prototype.hasOwnProperty.call(model, 'timezone')) {
        attributes.datetime.timezone = model.timezone
      }

      if (Object.prototype.hasOwnProperty.call(model, 'dateFormat')) {
        attributes.datetime.date_format = model.dateFormat
      }

      if (Object.prototype.hasOwnProperty.call(model, 'timeFormat')) {
        attributes.datetime.time_format = model.timeFormat
      }
    }

    return attributes
  }

  getRelationships(model: TJsonaModel): TJsonaRelationships {
    if (
      !Object.prototype.hasOwnProperty.call(model, RELATIONSHIP_NAMES_PROP) ||
      !Array.isArray(model[RELATIONSHIP_NAMES_PROP])
    ) {
      return {}
    }

    const relationshipNames = model[RELATIONSHIP_NAMES_PROP]

    const relationships: { [index: string]: any } = {}

    relationshipNames
      .forEach((relationName: string) => {
        const kebabName = relationName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`)

        if (model[relationName] !== undefined) {
          if (Array.isArray(model[relationName])) {
            relationships[kebabName] = model[relationName]
              .map((item: TJsonaModel) => {
                return {
                  id: item.id,
                  type: item.type,
                }
              })
          } else if (typeof model[relationName] === 'object' && model[relationName] !== null) {
            relationships[kebabName] = {
              id: model[relationName].id,
              type: model[relationName].type,
            }
          }
        }
      })

    if (Object.prototype.hasOwnProperty.call(model, 'accountId')) {
      const account = Account.find(model.accountId)

      if (account !== null) {
        relationships.account = {
          id: account.id,
          type: account.type,
        }
      }
    }

    return relationships
  }
}

export class JsonApiPropertiesMapper extends JsonPropertiesMapper implements IJsonPropertiesMapper {
  private caseRegExp = '_([a-z0-9])'

  createModel(type: string): TJsonaModel {
    return {type}
  }

  setId(model: TJsonaModel, id: string): void {
    Object.assign(model, {id})
  }

  setAttributes(model: TJsonaModel, attributes: TAnyKeyValueObject): void {
    const regex = new RegExp(this.caseRegExp, 'g')

    Object.keys(attributes).forEach((propName) => {
      const camelName = propName.replace(regex, g => g[1].toUpperCase())

      if (typeof attributes[propName] === 'object' && attributes[propName] !== null) {
        Object.keys(attributes[propName]).forEach((subPropName) => {
          const camelSubName = subPropName.replace(regex, g => g[1].toUpperCase())

          Object.assign(model, {[camelSubName]: attributes[propName][subPropName]})
        })
      } else {
        Object.assign(model, {[camelName]: attributes[propName]})
      }
    })

    // Entity received via api is not a draft entity
    Object.assign(model, {draft: false})
  }

  setRelationships(model: TJsonaModel, relationships: TJsonaRelationships): void {
    Object.keys(relationships)
      .forEach((propName) => {
        const regex = new RegExp(this.caseRegExp, 'g')
        const camelName = propName.replace(regex, g => g[1].toUpperCase())

        if (typeof relationships[propName] === 'function') {
          defineRelationGetter(model, propName, <TJsonaRelationshipBuild>relationships[propName])
        } else {
          const relation = clone(relationships[propName])

          if (Array.isArray(relation)) {
            Object.assign(
              model,
              {
                [camelName]: relation.map((item: TJsonaModel) => {
                  return this.transformAccount(item)
                }),
              },
            )
          } else if (get(relation, 'type') === AccountEntityTypes.USER) {
            Object.assign(model, {accountId: get(relation, 'id')})
          } else {
            Object.assign(model, {[camelName]: relation})
          }
        }
      })

    const newNames = Object.keys(relationships)
    const currentNames = model[RELATIONSHIP_NAMES_PROP]

    if (currentNames && currentNames.length) {
      Object.assign(model, {[RELATIONSHIP_NAMES_PROP]: [...currentNames, ...newNames].filter((value, i, self) => self.indexOf(value) === i)})
    } else {
      Object.assign(model, {[RELATIONSHIP_NAMES_PROP]: newNames})
    }
  }

  transformAccount(item: TJsonaModel): TJsonaModel {
    if (Object.prototype.hasOwnProperty.call(item, 'account')) {
      Object.assign(item, {accountId: item.account.id})
      Reflect.deleteProperty(item, 'account')
    }

    return item
  }
}
