// Function to auto-fill the current date in the Request Date field
window.onload = function () {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const currentDate = `${year}-${month}-${day}`;
    
    const requestDateField = document.getElementById('requestDate');
    if (requestDateField) {
        requestDateField.value = currentDate;
    }
};

// Helper function to get elements by ID
function getById(id) {
    return document.getElementById(id);
}

// Function to preview uploaded image
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const placeholder = getById('id-placeholder');
            const videoPreview = getById('id-preview');
            if (placeholder) {
                placeholder.src = e.target.result;
                placeholder.style.display = 'block';
            }
            if (videoPreview) {
                videoPreview.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    }
}

// Helper function to stop video stream
function stopVideoStream(video) {
    if (video && video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
}

// Handle Open Camera
const openCameraButton = getById('open-reason-camera-btn');
if (openCameraButton) {
    openCameraButton.addEventListener('click', function () {
        const video = getById('id-preview');
        const placeholder = getById('id-placeholder');
        const closeButton = getById('close-id-camera-btn');
        const captureButton = getById('capture-reason-btn');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.error("Camera access is not supported by this browser.");
            alert("Camera access is not supported by your browser.");
            return;
        }

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                if (video) {
                    video.srcObject = stream;
                    video.style.display = 'block';
                }
                if (placeholder) placeholder.style.display = 'none';
                if (closeButton) closeButton.style.display = 'block';
                if (captureButton) captureButton.style.display = 'block';
            })
            .catch(err => {
                console.error("Error accessing the camera: ", err);
                alert("Could not access the camera. Please check your device permissions.");
            });
    });
}

// Capture photo and display it in the photo preview
const captureButton = getById('capture-reason-btn');
if (captureButton) {
    captureButton.addEventListener('click', function () {
        const video = getById('id-preview');
        const placeholder = getById('id-placeholder');

        if (video && placeholder) {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            placeholder.src = canvas.toDataURL('image/png');
            placeholder.style.display = 'block';
            video.style.display = 'none';
            stopVideoStream(video);

            getById('close-id-camera-btn').style.display = 'none';
            captureButton.style.display = 'none';
        }
    });
}

// Close the camera and stop the stream
const closeButton = getById('close-id-camera-btn');
if (closeButton) {
    closeButton.addEventListener('click', function () {
        const video = getById('id-preview');
        const placeholder = getById('id-placeholder');

        stopVideoStream(video);

        if (video) video.style.display = 'none';
        if (placeholder) placeholder.style.display = 'block';
        closeButton.style.display = 'none';
        if (captureButton) captureButton.style.display = 'none';
    });
}


// Dropdown population functions
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



// Add event listeners
if (birthMonthSelect && birthYearSelect) {
    birthMonthSelect.addEventListener('change', handleDateChange);
    birthYearSelect.addEventListener('change', handleDateChange);
}

