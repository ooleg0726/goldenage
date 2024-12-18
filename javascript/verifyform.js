document.addEventListener('DOMContentLoaded', function () {
    const rrnVerify = document.getElementById('rrnVerify'); // Radio button for SCID verification
    const nameDobVerify = document.getElementById('nameDobVerify'); // Radio button for Name and DOB verification
    const scidSection = document.getElementById('scidSection');
    const nameDobSection = document.getElementById('nameDobSection');
    const cancelBtn = document.getElementById('cancelBtn');
    const submitBtn = document.getElementById('submitBtn');
    const cancelNameDobBtn = document.getElementById('cancelNameDobBtn');
    const submitNameDobBtn = document.getElementById('submitNameDobBtn');
    const birthMonthSelect = document.getElementById('birthMonth');
    const birthDaySelect = document.getElementById('birthDay');
    const birthYearSelect = document.getElementById('birthYear');

    const months = [
        { value: '', text: 'Select month' },
        { value: '01', text: 'January' },
        { value: '02', text: 'February' },
        { value: '03', text: 'March' },
        { value: '04', text: 'April' },
        { value: '05', text: 'May' },
        { value: '06', text: 'June' },
        { value: '07', text: 'July' },
        { value: '08', text: 'August' },
        { value: '09', text: 'September' },
        { value: '10', text: 'October' },
        { value: '11', text: 'November' },
        { value: '12', text: 'December' }
    ];

    const startYear = 1964; // Starting year
    const currentYear = new Date().getFullYear();
    const years = [];

    for (let year = startYear; year <= currentYear; year++) {
        years.push({ value: year.toString(), text: year.toString() });
    }

    function populateMonths() {
        months.forEach(month => {
            const option = document.createElement('option');
            option.value = month.value;
            option.text = month.text;
            birthMonthSelect.add(option);
        });
    }

    function populateYears() {
        years.forEach(year => {
            const option = document.createElement('option');
            option.value = year.value;
            option.text = year.text;
            birthYearSelect.add(option);
        });
    }

    function populateDays(month, year) {
        const daysInMonth = new Date(year, month, 0).getDate();
        birthDaySelect.innerHTML = '<option value="">Select date</option>'; // Clear previous options

        for (let day = 1; day <= daysInMonth; day++) {
            const option = document.createElement('option');
            option.value = day < 10 ? `0${day}` : day;
            option.text = day;
            birthDaySelect.add(option);
        }
    }

    function handleDateChange() {
        const month = birthMonthSelect.value;
        const year = birthYearSelect.value;
        if (month && year) {
            populateDays(month, year);
        }
    }

    // Populate months and years on page load
    populateMonths();
    populateYears();

    // Add event listeners
    birthMonthSelect.addEventListener('change', handleDateChange);
    birthYearSelect.addEventListener('change', handleDateChange);

    // Toggle SCID section based on verification method
    rrnVerify.addEventListener('change', function () {
        if (rrnVerify.checked) {
            scidSection.style.display = 'block';  // Show SCID section
            nameDobSection.style.display = 'none'; // Hide Name and DOB section
            nameDobVerify.checked = false; // Uncheck other verification option
        }
    });

    nameDobVerify.addEventListener('change', function () {
        if (nameDobVerify.checked) {
            scidSection.style.display = 'none'; // Hide SCID section
            nameDobSection.style.display = 'block';  // Show Name and DOB section
            rrnVerify.checked = false; // Uncheck other verification option
        }
    });

    // Hide SCID section and uncheck the radio button when Cancel is clicked
    cancelBtn.addEventListener('click', function () {
        scidSection.style.display = 'none';
        rrnVerify.checked = false;
    });

    // Hide Name and Birthday section and uncheck the radio button when Cancel is clicked
    cancelNameDobBtn.addEventListener('click', function () {
        nameDobSection.style.display = 'none';
        nameDobVerify.checked = false;
    });

    // Implement SCID form submission logic
    submitBtn.addEventListener('click', function () {
        const scidInputValue = document.getElementById('scidInput').value;
        alert('Submitted: ' + scidInputValue);
    });

    // Implement Name and Birthday form submission logic
    submitNameDobBtn.addEventListener('click', function () {
        const lastname = document.getElementById('lastname').value;
        const firstname = document.getElementById('firstname').value;
        const middleInitial = document.getElementById('middleInitial').value;
        const suffix = document.getElementById('suffix').value;
        const birthMonth = document.getElementById('birthMonth').value;
        const birthDay = document.getElementById('birthDay').value;
        const birthYear = document.getElementById('birthYear').value;

        alert(`Submitted Name and Birthday Verification:\nName: ${lastname}, ${firstname} ${middleInitial}\nSuffix: ${suffix}\nBirthday: ${birthMonth} ${birthDay}, ${birthYear}`);
    });
});
