// Ensure Lang exists (loaded from messages.js)
window.Lang = window.Lang || {};

// Default locale (from <html lang="en"> or fallback)
Lang.locale = document.documentElement.lang || 'pt';

/**
 * Get translation value
 * Example: Lang.get('js.welcome')
 */
Lang.get = function (key, replace = {}) {
    const keys = key.split('.');

    // Get nested value: Lang[locale][file][key]
    let value = keys.reduce((obj, i) => obj?.[i], this[this.locale]);

    // If not found, return key
    if (!value) return key;

    // Replace variables (:name)
    Object.keys(replace).forEach(k => {
        value = value.replace(new RegExp(':' + k, 'g'), replace[k]);
    });

    return value;
};

/**
 * Check if translation exists
 */
Lang.has = function (key) {
    return this.get(key) !== key;
};

/**
 * Change current locale
 * Example: Lang.setLocale('es')
 */
Lang.setLocale = function (locale) {
    this.locale = locale;
};

/**
 * Handle pluralization
 * Example:
 * 'apple' => 'One apple|Many apples'
 */
Lang.choice = function (key, count, replace = {}) {
    let value = this.get(key, replace);

    if (!value) return key;

    let parts = value.split('|');

    if (parts.length === 1) return value;

    return count > 1 ? parts[1] : parts[0];
};

/**
 * Debug helper (optional)
 */
Lang.dump = function () {
    console.log(this);
};
