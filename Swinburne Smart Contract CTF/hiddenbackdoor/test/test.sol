// SPDX-License-Identifier: MIT
pragma solidity ^0.8.18;

import "forge-std/Test.sol"; // Foundry library
import "../src/hiddenbackdoor.sol";

contract TreasureVaultTest is Test {
    TreasureVault public vault;

    address public attacker =
        address(0x1234567890123456789012345678901234567890);

    function setUp() public {
        // Deploy the TreasureVault contract
        vault = new TreasureVault();
    }

    function testInitiateExpedition() public {
        uint256 timestamp = block.timestamp;
        // Log the current explorer before calling initiateExpedition
        address initialExplorer = vault.checkCurrentExplorer();
        console.log("Initial Explorer Address: ", initialExplorer);
        // Call initiateExpedition
        vault.initiateExpedition(attacker, timestamp);
        // Log the current explorer after calling initiateExpedition
        address updatedExplorer = vault.checkCurrentExplorer();
        console.log("Updated Explorer Address: ", updatedExplorer);
        // Assert that the explorer was updated
        assertEq(
            updatedExplorer,
            attacker,
            "currentExplorer was not updated correctly"
        );
    }
}
