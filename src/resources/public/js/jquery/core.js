/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

/* global Symbol */
// Defining this global in .eslintrc.json would create a danger of using the global
// unguarded in another place, it seems safer to define global only for this module

define([
  './var/arr',
  './var/document',
  './var/getProto',
  './var/slice',
  './var/concat',
  './var/push',
  './var/indexOf',
  './var/class2type',
  './var/toString',
  './var/hasOwn',
  './var/fnToString',
  './var/ObjectFunctionString',
  './var/support',
  './var/isFunction',
  './var/isWindow',
  './core/DOMEval',
  './core/toType'
], function (arr, document, getProto, slice, concat, push, indexOf,
  class2type, toString, hasOwn, fnToString, ObjectFunctionString,
  support, isFunction, isWindow, DOMEval, toType) {
  'use strict'

  const
    version = '3.4.1'

  // Define a local copy of jQuery
  var jQuery = function (selector, context) {
    // The jQuery object is actually just the init constructor 'enhanced'
    // Need init if jQuery is called (just allow error to be thrown if not included)
    return new jQuery.fn.init(selector, context)
  }

  // Support: Android <=4.0 only
  // Make sure we trim BOM and NBSP
  const rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g

  jQuery.fn = jQuery.prototype = {

    // The current version of jQuery being used
    jquery: version,

    constructor: jQuery,

    // The default length of a jQuery object is 0
    length: 0,

    toArray: function () {
      return slice.call(this)
    },

    // Get the Nth element in the matched element set OR
    // Get the whole matched element set as a clean array
    get: function (num) {
      // Return all the elements in a clean array
      if (num == null) {
        return slice.call(this)
      }

      // Return just the one element from the set
      return num < 0 ? this[num + this.length] : this[num]
    },

    // Take an array of elements and push it onto the stack
    // (returning the new matched element set)
    pushStack: function (elems) {
      // Build a new jQuery matched element set
      const ret = jQuery.merge(this.constructor(), elems)

      // Add the old object onto the stack (as a reference)
      ret.prevObject = this

      // Return the newly-formed element set
      return ret
    },

    // Execute a callback for every element in the matched set.
    each: function (callback) {
      return jQuery.each(this, callback)
    },

    map: function (callback) {
      return this.pushStack(jQuery.map(this, function (elem, i) {
        return callback.call(elem, i, elem)
      }))
    },

    slice: function () {
      return this.pushStack(slice.apply(this, arguments))
    },

    first: function () {
      return this.eq(0)
    },

    last: function () {
      return this.eq(-1)
    },

    eq: function (i) {
      const len = this.length
      const j = +i + (i < 0 ? len : 0)
      return this.pushStack(j >= 0 && j < len ? [this[j]] : [])
    },

    end: function () {
      return this.prevObject || this.constructor()
    },

    // For internal use only.
    // Behaves like an Array's method, not like a jQuery method.
    push: push,
    sort: arr.sort,
    splice: arr.splice
  }

  jQuery.extend = jQuery.fn.extend = function () {
    let options
    let name
    let src
    let copy
    let copyIsArray
    let clone
    let target = arguments[0] || {}
    let i = 1
    const length = arguments.length
    let deep = false

    // Handle a deep copy situation
    if (typeof target === 'boolean') {
      deep = target

      // Skip the boolean and the target
      target = arguments[i] || {}
      i++
    }

    // Handle case when target is a string or something (possible in deep copy)
    if (typeof target !== 'object' && !isFunction(target)) {
      target = {}
    }

    // Extend jQuery itself if only one argument is passed
    if (i === length) {
      target = this
      i--
    }

    for (; i < length; i++) {
      // Only deal with non-null/undefined values
      if ((options = arguments[i]) != null) {
        // Extend the base object
        for (name in options) {
          copy = options[name]

          // Prevent Object.prototype pollution
          // Prevent never-ending loop
          if (name === '__proto__' || target === copy) {
            continue
          }

          // Recurse if we're merging plain objects or arrays
          if (deep && copy && (jQuery.isPlainObject(copy) ||
            (copyIsArray = Array.isArray(copy)))) {
            src = target[name]

            // Ensure proper type for the source value
            if (copyIsArray && !Array.isArray(src)) {
              clone = []
            } else if (!copyIsArray && !jQuery.isPlainObject(src)) {
              clone = {}
            } else {
              clone = src
            }
            copyIsArray = false

            // Never move original objects, clone them
            target[name] = jQuery.extend(deep, clone, copy)

            // Don't bring in undefined values
          } else if (copy !== undefined) {
            target[name] = copy
          }
        }
      }
    }

    // Return the modified object
    return target
  }

  jQuery.extend({

    // Unique for each copy of jQuery on the page
    expando: 'jQuery' + (version + Math.random()).replace(/\D/g, ''),

    // Assume jQuery is ready without the ready module
    isReady: true,

    error: function (msg) {
      throw new Error(msg)
    },

    noop: function () {},

    isPlainObject: function (obj) {
      let proto, Ctor

      // Detect obvious negatives
      // Use toString instead of jQuery.type to catch host objects
      if (!obj || toString.call(obj) !== '[object Object]') {
        return false
      }

      proto = getProto(obj)

      // Objects with no prototype (e.g., `Object.create( null )`) are plain
      if (!proto) {
        return true
      }

      // Objects with prototype are plain iff they were constructed by a global Object function
      Ctor = hasOwn.call(proto, 'constructor') && proto.constructor
      return typeof Ctor === 'function' && fnToString.call(Ctor) ===
        ObjectFunctionString
    },

    isEmptyObject: function (obj) {
      let name

      for (name in obj) {
        return false
      }
      return true
    },

    // Evaluates a script in a global context
    globalEval: function (code, options) {
      DOMEval(code, { nonce: options && options.nonce })
    },

    each: function (obj, callback) {
      let length
      let i = 0

      if (isArrayLike(obj)) {
        length = obj.length
        for (; i < length; i++) {
          if (callback.call(obj[i], i, obj[i]) === false) {
            break
          }
        }
      } else {
        for (i in obj) {
          if (callback.call(obj[i], i, obj[i]) === false) {
            break
          }
        }
      }

      return obj
    },

    // Support: Android <=4.0 only
    trim: function (text) {
      return text == null
        ? ''
        : (text + '').replace(rtrim, '')
    },

    // results is for internal usage only
    makeArray: function (arr, results) {
      const ret = results || []

      if (arr != null) {
        if (isArrayLike(Object(arr))) {
          jQuery.merge(ret,
            typeof arr === 'string'
              ? [arr]
              : arr
          )
        } else {
          push.call(ret, arr)
        }
      }

      return ret
    },

    inArray: function (elem, arr, i) {
      return arr == null ? -1 : indexOf.call(arr, elem, i)
    },

    // Support: Android <=4.0 only, PhantomJS 1 only
    // push.apply(_, arraylike) throws on ancient WebKit
    merge: function (first, second) {
      const len = +second.length
      let j = 0
      let i = first.length

      for (; j < len; j++) {
        first[i++] = second[j]
      }

      first.length = i

      return first
    },

    grep: function (elems, callback, invert) {
      let callbackInverse
      const matches = []
      let i = 0
      const length = elems.length
      const callbackExpect = !invert

      // Go through the array, only saving the items
      // that pass the validator function
      for (; i < length; i++) {
        callbackInverse = !callback(elems[i], i)
        if (callbackInverse !== callbackExpect) {
          matches.push(elems[i])
        }
      }

      return matches
    },

    // arg is for internal usage only
    map: function (elems, callback, arg) {
      let length
      let value
      let i = 0
      const ret = []

      // Go through the array, translating each of the items to their new values
      if (isArrayLike(elems)) {
        length = elems.length
        for (; i < length; i++) {
          value = callback(elems[i], i, arg)

          if (value != null) {
            ret.push(value)
          }
        }

        // Go through every key on the object,
      } else {
        for (i in elems) {
          value = callback(elems[i], i, arg)

          if (value != null) {
            ret.push(value)
          }
        }
      }

      // Flatten any nested arrays
      return concat.apply([], ret)
    },

    // A global GUID counter for objects
    guid: 1,

    // jQuery.support is not used in Core but other projects attach their
    // properties to it so it needs to exist.
    support: support
  })

  if (typeof Symbol === 'function') {
    jQuery.fn[Symbol.iterator] = arr[Symbol.iterator]
  }

  // Populate the class2type map
  jQuery.each(
    'Boolean Number String Function Array Date RegExp Object Error Symbol'.split(
      ' '),
    function (i, name) {
      class2type['[object ' + name + ']'] = name.toLowerCase()
    })

  function isArrayLike (obj) {
    // Support: real iOS 8.2 only (not reproducible in simulator)
    // `in` check used to prevent JIT error (gh-2145)
    // hasOwn isn't used here due to false negatives
    // regarding Nodelist length in IE
    const length = !!obj && 'length' in obj && obj.length
    const type = toType(obj)

    if (isFunction(obj) || isWindow(obj)) {
      return false
    }

    return type === 'array' || length === 0 ||
      typeof length === 'number' && length > 0 && (length - 1) in obj
  }

  return jQuery
})
