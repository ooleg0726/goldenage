
document.getElementById('appointment-form').addEventListener('submit', function(e) {
    const timeInput = document.getElementById('appointment-time').value;
    const minTime = "09:00";
    const maxTime = "17:00";

    if (timeInput < minTime || timeInput > maxTime) {
        e.preventDefault();
        alert('Please select a time between 9:00 AM and 5:00 PM.');
    }
});

// Dropdown population functions
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

const startYear = 1964;
const endYear = 1900;

function populateMonths() {
    if (birthMonthSelect) {
        months.forEach(month => {
            const option = document.createElement('option');
            option.value = month.value;
            option.text = month.text;
            birthMonthSelect.add(option);
        });
    }
}

function populateYears() {
    if (birthYearSelect) {
        for (let year = startYear; year >= endYear; year--) {
            const option = document.createElement('option');
            option.value = year.toString();
            option.text = year.toString();
            birthYearSelect.add(option);
        }
    }
}

function populateDays(month, year) {
    if (birthDaySelect) {
        birthDaySelect.innerHTML = '<option value="">Select date</option>';
        const daysInMonth = new Date(year, month, 0).getDate();
        for (let day = 1; day <= daysInMonth; day++) {
            const option = document.createElement('option');
            option.value = day < 10 ? `0${day}` : day.toString();
            option.text = day;
            birthDaySelect.add(option);
        }
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

populateYears();

// Add event listeners
if (birthMonthSelect && birthYearSelect) {
    birthMonthSelect.addEventListener('change', handleDateChange);
    birthYearSelect.addEventListener('change', handleDateChange);
}