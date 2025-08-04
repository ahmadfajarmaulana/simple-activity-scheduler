<!DOCTYPE html>
<html>
<head>
    <title>Schedule Outdoor Activity</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px;
            padding: 6px 12px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Schedule Outdoor Activity</h1>

    @if(isset($success) && $success)
        <div class="alert alert-success">
            <h5 class="alert-heading">Activity Scheduled!</h5>
            <p><strong>Name:</strong> {{ $activity->name }}</p>
            <p><strong>Location:</strong> {{ $activity->location }}</p>
            <p><strong>Preferred Date:</strong> {{ $activity->preferred_date }}</p>
            <p><strong>Suggested Time:</strong> {{ $suggested_time }}</p>
            <p><strong>Weather:</strong> {{ $weather }}</p>

            @if(isset($weather_warning) && $weather_warning)
                <div class="alert alert-warning mt-3">
                    <strong>Warning:</strong> Cuaca buruk dalam 3 hari ke depan. Pertimbangkan untuk memilih tanggal lain.
                </div>
            @endif
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('activity.schedule') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Activity Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label for="location">Location (Kelurahan/Desa)</label>
                    <select id="location" name="location" class="form-control">
                        @if(old('location'))
                            <option selected value="{{ old('location') }}">{{ old('location') }}</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label for="preferred_date">Preferred Date</label>
                    <input type="date" class="form-control" id="preferred_date" name="preferred_date" value="{{ old('preferred_date') }}" required>
                </div>

                <label>
                    <input type="checkbox" name="simulate_bad_weather" {{ old('simulate_bad_weather') ? 'checked' : '' }}>
                    Simulasikan cuaca buruk
                </label><br><br>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    @if(isset($forecast_3_days))
        <div class="mb-4">
            <h4>Prakiraan Cuaca 3 Hari ke Depan</h4>
            <ul class="list-group">
                @foreach($forecast_3_days as $slot)
                    <li class="list-group-item">{{ $slot['datetime'] }} – {{ $slot['weather'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($activities) && $activities->count())
        <div class="mb-4">
            <h4>Recent Activities</h4>
            <ul class="list-group">
                @foreach($activities as $a)
                    <li class="list-group-item">
                        <strong>{{ $a->name }}</strong> at {{ $a->location }} ({{ $a->preferred_date }}) –
                        <em>{{ $a->suggested_time_slot }}</em> ({{ $a->weather }})
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="text-muted mt-4" style="font-size: 12px;">
        Weather data by <strong>BMKG</strong>
    </p>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#location').select2({
            placeholder: 'Search location...',
            ajax: {
                url: '{{ route("wilayah.search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(item => ({
                            id: item.kode,
                            text: item.nama
                        }))
                    };
                },
                cache: true
            }
        });
    });
</script>
</body>
</html>
