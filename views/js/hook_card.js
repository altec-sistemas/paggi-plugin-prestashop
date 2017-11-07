$("form.paggi").card({

    container: '.card-wrapper', // *required*

    formSelectors: {
        numberInput: 'input[name=PAGGI_CARD_NUMBER]', // optional — default input[name="number"]
        expiryInput: 'input[name=PAGGI_CARD_EXPIRATE]', // optional — default input[name="expiry"]
        cvcInput: 'input[name=PAGGI_CARD_CVC]', // optional — default input[name="cvc"]
        nameInput: 'input[name=PAGGI_CARD_HOLDER_NAME]' // optional - defaults input[name="name"]
    },

    messages: {
        validDate: 'valid\ndate', // optional - default 'valid\nthru'
        monthYear: 'mm/yyyy', // optional - default 'month/year'
    },

    // Default placeholders for rendered fields - optional
    placeholders: {
        number: '•••• •••• •••• ••••',
        name: mask_name,
        expiry: '••/••',
        cvc: '•••'
    },

});