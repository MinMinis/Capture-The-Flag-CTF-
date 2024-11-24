// SPDX-License-Identifier: MIT
pragma solidity ^0.8.18;

contract TreasureVault {
    uint256 public totalTreasure = 2500;
    address public currentExplorer;
    address public vaultKeeper;
    bool public isActive = true;
    uint256 private lastAccessTime;

    event ExplorerSelected(address indexed explorer, uint256 timestamp);

    modifier onlyDuringExpedition() {
        require(msg.sender == currentExplorer, "Not authorized explorer");
        _;
    }

    constructor() {
        vaultKeeper = msg.sender;
    }

    function initiateExpedition(
        address explorer,
        uint256 timestamp
    ) public onlyDuringExpedition {
        require(isActive, "Expedition ended");
        lastAccessTime = timestamp;
        currentExplorer = explorer;  // Directly update currentExplorer

        emit ExplorerSelected(explorer, timestamp);
    }

    function checkCurrentExplorer() public view returns (address) {
        return currentExplorer;
    }

    function getLastAccessTime() public view returns (uint256) {
        return lastAccessTime;
    }

    function toggleExpeditionStatus() public {
        require(msg.sender == vaultKeeper, "Not authorized");
        isActive = !isActive;
    }
}
