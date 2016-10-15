# URL Schema

## u - user
##### Users [all].

###### Standard

```
/                                       All
/id         /[###]                      by ID
/dci        /[###]                      by DCI
/???        /???    /s                  STORES by USER
/???        /???    /t                  TOURNAMENTS by USER
/???        /???    /t      /played     TOURNAMENTS by PLAYER where PLAYER = player && USER = PLAYER
/???        /???    /t      /judged     TOURNAMENTS by PLAYER where PLAYER = judge && USER = PLAYER
/???        /???    /t      /owned      TOURNAMENTS by USER where USER = TOURNAMENT.organizer

```

###### Potential

<table style='font-family: "Courier New", Courier, monospace;'>
    <tr>
        <td>/[###]</td>
        <td></td>
        <td></td>
        <td></td>
        <td>by ID</td>
    </tr>
</table>

## t - tournament
##### Tournaments [all].

###### Standard

<table style='font-family: "Courier New", Courier, monospace;'>
    <tr>
        <td>/</td>
        <td></td>
        <td></td>
        <td></td>
        <td>All</td>
    </tr>
    <tr>
        <td>/id</td>
        <td>/[###]</td>
        <td></td>
        <td></td>
        <td>by NUMERIC</td>
    </tr>
    <tr>
        <td>/name</td>
        <td>/[???]</td>
        <td></td>
        <td></td>
        <td>by ALPHANUMERIC</td>
    </tr>
    <tr>
        <td>/???</td>
        <td>/???</td>
        <td>/s</td>
        <td></td>
        <td>STORES by USER</td>
    </tr>
    <tr>
        <td>/???</td>
        <td>/???</td>
        <td>/t</td>
        <td></td>
        <td>TOURNAMENTS by USER</td>
    </tr>
    <tr>
        <td>/???</td>
        <td>/???</td>
        <td>/t</td>
        <td>/played</td>
        <td>TOURNAMENTS by PLAYER where PLAYER = player && USER = PLAYER</td>
    </tr>
    <tr>
        <td>/???</td>
        <td>/???</td>
        <td>/t</td>
        <td>/judged</td>
        <td>TOURNAMENTS by PLAYER where PLAYER = judge && USER = PLAYER</td>
    </tr>
    <tr>
        <td>/???</td>
        <td>/???</td>
        <td>/t</td>
        <td>/owned</td>
        <td>TOURNAMENTS by USER where USER = TOURNAMENT.organizer</td>
    </tr>
</table>

###### Potential

<table style='font-family: "Courier New", Courier, monospace;'>
    <tr>
        <td>/[###]</td>
        <td></td>
        <td></td>
        <td></td>
        <td>by ID</td>
    </tr>
</table>