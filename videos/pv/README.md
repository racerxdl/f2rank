     ____                 _                __     ___     _                
    |  _ \ _ __ _____   _(_) _____      __ \ \   / (_) __| | ___  ___  ___ 
    | |_) | '__/ _ \ \ / / |/ _ \ \ /\ / /  \ \ / /| |/ _` |/ _ \/ _ \/ __|
    |  __/| | |  __/\ V /| |  __/\ V  V /    \ V / | | (_| |  __/ (_) \__ \
    |_|   |_|  \___| \_/ |_|\___| \_/\_/      \_/  |_|\__,_|\___|\___/|___/
                                                                           

In this folder you should put the preview videos that **Pump Selector** will use. Since we don't have any common video format over all browsers we should have here 3 formats that one of them are supporter over all browsers. The formats are **webm** (Supported Chrome), **ogg** (Supported by Chrome and Firefox), **x264** (MPEG4 supported by Chrome and Internet Explorer).

Maybe the browsers changed their support to video formats, the Pump Selector auto-selects the supported video giving preference for WebM (that is the best format we can have for that).

The videos should be named like:

    1005.mp4
    1005.webm
    1005.ogg

1005 is the Hex of the SongID