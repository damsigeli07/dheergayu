window.PatientFormUtils = (function () {
    function isRequired(value) {
        return String(value ?? '').trim() !== '';
    }

    function matches(value, pattern) {
        return pattern.test(String(value ?? '').trim());
    }

    function toDigits(value, maxLength) {
        const digits = String(value ?? '').replace(/\D/g, '');
        return typeof maxLength === 'number' ? digits.slice(0, maxLength) : digits;
    }

    function validateRules(formData, rules) {
        for (const [field, rule] of Object.entries(rules)) {
            const value = String(formData.get(field) ?? '').trim();
            if (rule.required && !isRequired(value)) {
                return rule.message || `${field} is required.`;
            }
            if (rule.pattern && value && !matches(value, rule.pattern)) {
                return rule.message || `${field} is invalid.`;
            }
            if (rule.custom && typeof rule.custom === 'function') {
                const customError = rule.custom(value, formData);
                if (customError) return customError;
            }
        }
        return '';
    }

    // ALTER TABLE contact_submissions
// ADD COLUMN comments TEXT NULL AFTER message;
    //db-school
    // ALTER TABLE patient_info
    // ADD COLUMN school_name VARCHAR(255) NULL DEFAULT NULL AFTER address;

    return {
        isRequired,
        matches,
        toDigits,
        validateRules
    };

        return {
        //profile
        db_1,
        patientModel_3,
        api_1,
        view_2
    };

        return {
        //contact
        db_1,
        view_2,
        api_5,
        admincontact,
        myinquiries
    };

        return {
        //appointment
        db_1,
        channeling,
        patient_app,
        api_book_con,
        app_model
    };

})();
