<!-- jquery latest version -->
<script src="{{ asset('backend/assets/js/vendor/jquery-2.2.4.min.js') }}"></script>
<!-- bootstrap 4 js -->
<script src="{{ asset('backend/assets/js/popper.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/metisMenu.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/jquery.slicknav.min.js') }}"></script>

<!-- start chart js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>
<!-- start highcharts js -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<!-- start zingchart js -->
<script src="https://cdn.zingchart.com/zingchart.min.js"></script>
<script>
    zingchart.MODULESDIR = "https://cdn.zingchart.com/modules/";
    ZC.LICENSE = ["569d52cefae586f634c54f86dc99e6a9", "ee6b7db5b51705a13dc2339db3edaf6d"];
</script>
<!-- all line chart activation -->
<script src="{{ asset('backend/assets/js/line-chart.js') }}"></script>
<!-- all pie chart -->
<script src="{{ asset('backend/assets/js/pie-chart.js') }}"></script>
<!-- others plugins -->
<script src="{{ asset('backend/assets/js/plugins.js') }}"></script>
<script src="{{ asset('backend/assets/js/scripts.js') }}"></script>

<script>
    function markNotificationAsRead(notificationId, url) {
        fetch(`/admin/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Include CSRF token if using Laravel
                },
                body: JSON.stringify({
                    // You can send any data you need in the body, if necessary
                })
            })
            .then(response => {
                if (response.ok) {
                    console.log('Notification marked as read');
                    // After marking as read, redirect the user
                    window.location.href = url;
                } else {
                    console.error('Error marking notification as read');
                }
            })
            .catch(error => {
                console.error('Error with the request:', error);
            });
    }
</script>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        let competitionDropdown = document.querySelector("select[name='competition_id']");
        if (competitionDropdown) {

            getClubsByCompetition(competitionDropdown.value); // Load clubs for default competition
        }
    });

    function getClubsByCompetition(competitionId) {
        if (!competitionId) {
            document.getElementById('away_club_id').innerHTML = '<option value="">Select Home Club</option>';
            document.getElementById('home_club_id').innerHTML = '<option value="">Select Away Club</option>';
            return;
        }

        homeClubDropdown = document.getElementById("home_club_id_default")
        awayClubDropdown = document.getElementById("away_club_id_default")

        let selectedHomeClub = homeClubDropdown ? homeClubDropdown.value : null;
        let selectedAwayClub = awayClubDropdown ? awayClubDropdown.value : null;

        document.getElementById('home_club_id').disabled = true;
        document.getElementById('away_club_id').disabled = true;
        document.getElementById('submit_match').disabled = true;

        fetch(`/admin/clubs_by_competition/${competitionId}`)
            .then(response => response.json())
            .then(data => {
                let clubDropdown = document.getElementById('home_club_id');
                let clubDropdown2 = document.getElementById('away_club_id');

                clubDropdown.innerHTML = '<option value="">Select Club</option>'; // Reset dropdown

                data.forEach(club => {
                    let option = document.createElement('option');
                    option.value = club.id;
                    option.textContent = club.name;
                    if (selectedHomeClub && club.id == selectedHomeClub) {
                        option.selected = true;
                    }
                    clubDropdown.appendChild(option);
                });

                clubDropdown2.innerHTML = '<option value="">Select Club</option>'; // Reset dropdown

                data.forEach(club => {
                    let option = document.createElement('option');
                    option.value = club.id;
                    option.textContent = club.name;
                    if (selectedAwayClub && club.id == selectedAwayClub) {
                        option.selected = true;
                    }
                    clubDropdown2.appendChild(option);
                });

                document.getElementById('home_club_id').disabled = false;
                document.getElementById('away_club_id').disabled = false;
                document.getElementById('submit_match').disabled = false;
            })
            .catch(error => {
                console.error('Error fetching clubs:', error);

                document.getElementById('home_club_id').disabled = false;
                document.getElementById('away_club_id').disabled = false;
                document.getElementById('submit_match').disabled = false;

            });
    }
</script>

<script>
    function validateClubs() {
        var homeClubId = document.getElementById('home_club_id').value;
        var awayClubId = document.getElementById('away_club_id').value;

        // Clear any previous error message
        var errorMessage = document.getElementById('club-error');
        if (errorMessage) {
            errorMessage.remove();
        }

        if (homeClubId && awayClubId && homeClubId === awayClubId) {
            // Display error if home and away clubs are the same
            var errorElement = document.createElement('div');
            errorElement.id = 'club-error';
            errorElement.classList.add('text-danger');
            errorElement.innerHTML = 'Home Club cannot be the same as Away Club.';
            document.getElementById('submit_match').disabled = true;

            // Insert the error message after the away club select element
            document.getElementById('away_club_id').insertAdjacentElement('afterend', errorElement);

            // Optionally, you can prevent form submission or disable the submit button here.
            return false; // Returning false will stop the form submission
        }

        document.getElementById('submit_match').disabled = false;

        return true; // If the validation passes
    }

    // Call the validation on form submission if needed
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', function(event) {
            if (!validateClubs()) {
                event.preventDefault(); // Prevent form submission if validation fails
            }
        });
    }
</script>

<script>
    (function () {
        function isMobile() {
            return window.matchMedia && window.matchMedia('(max-width: 992px)').matches;
        }

        function initSidebarToggle() {
            var pageContainer = document.querySelector('.page-container');
            if (!pageContainer) return;

            // Default behavior: collapsed on mobile, expanded on desktop.
            if (isMobile()) {
                pageContainer.classList.add('sbar_collapsed');
            } else {
                pageContainer.classList.remove('sbar_collapsed');
            }
        }

        // Run immediately (scripts are loaded at bottom; DOMContentLoaded may have already fired).
        initSidebarToggle();
        // Also run on DOM ready for safety.
        document.addEventListener('DOMContentLoaded', initSidebarToggle);
        window.addEventListener('resize', initSidebarToggle);
    })();
</script>