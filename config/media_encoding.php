<?php

return [
    'allowed_input_extensions' => [
        'audio' => [
            'wav',
            'aif',
            'wma',
            'm4a',
            'mp3'
        ],
        'video' => [
            'mp4',
            'webm',
            'ogg',
            'ogv',
            'avi',
            'mov',
            'mkv',
            '3gp'
        ],
    ],
    'output_options' => [
        'audio' => [
            'mp3' => '-y -vn -ar 44100 -ac 2 -ab 192 -f mp3',
        ],
        'video' => [
            'mp4' => '-y -c:a aac -b:a 128k -c:v libx264 -crf 23 -f mp4',
            'webm' => '-y -vcodec libvpx -qscale:v 5  -acodec libvorbis -qscale:a 5 -f webm',
            'ogv' => '-y -codec:v libtheora -qscale:v 5 -codec:a libvorbis -qscale:a 5 -f ogg',
        ]
    ],
];