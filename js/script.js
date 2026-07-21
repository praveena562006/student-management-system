/*
========================================
MARK ALL ATTENDANCE
========================================
*/

function markAll(status) {

    const radios =
        document.querySelectorAll(
            'input[type="radio"][value="' + status + '"]'
        );

    radios.forEach(function(radio) {

        radio.checked = true;

    });

}


/*
========================================
DELETE CONFIRMATION
========================================
*/

function confirmDelete() {

    return confirm(
        "Are you sure you want to delete this record?"
    );

}


/*
========================================
SHOW / HIDE PASSWORD
========================================
*/

function togglePassword() {

    const password =
        document.getElementById("password");

    if (!password) {
        return;
    }

    if (password.type === "password") {

        password.type = "text";

    } else {

        password.type = "password";

    }

}


/*
========================================
LIVE STUDENT SEARCH
========================================
*/

function liveSearch() {

    const input =
        document.getElementById("studentSearch");

    const table =
        document.getElementById("studentTable");

    if (!input || !table) {
        return;
    }

    const filter =
        input.value.toLowerCase();

    const rows =
        table.getElementsByTagName("tr");


    for (let i = 1; i < rows.length; i++) {

        const text =
            rows[i].textContent.toLowerCase();

        if (text.includes(filter)) {

            rows[i].style.display = "";

        } else {

            rows[i].style.display = "none";

        }

    }

}