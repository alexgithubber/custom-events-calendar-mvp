<!DOCTYPE html>
<html lang="en">
<head>
    <title>CustomizedCalendar.com</title>
</head>
<body>
<h1>{{ $mailData['title'] }}</h1>
<p>{{ $mailData['main_text'] }}</p>

<ul>
    <li>Date: {{ $mailData['event_date'] }}</li>
    <li>Location: {{ $mailData['location'] }}</li>
</ul>

<p>Enjoy the event!</p>
</body>
</html>
