// SPDX-License-Identifier: MIT
pragma solidity ^0.8.18;

import "../lib/openzeppelin-contracts/contracts/access/Ownable.sol";

contract TreasureVault is Ownable {
    uint256 public totalTreasure = 2500;
    address public currentExplorer;
    bool public isActive = true;
    uint256 private lastAccessTime;
    event ExplorerSelected(address indexed explorer, uint256 timestamp);

    modifier onlyDuringExpedition() {
        require(
            msg.sender == currentExplorer,
            "Not authorized during expedition"
        );
        _;
    }

    // Constructor now correctly passes msg.sender to the Ownable constructor
    constructor() Ownable() {}

    function initiateExpedition(
        address explorer,
        uint256 timestamp
    ) public onlyOwner {
        require(isActive, "Expedition ended");
        lastAccessTime = timestamp;

        // Safely set the currentExplorer
        currentExplorer = explorer;

        emit ExplorerSelected(explorer, timestamp);
    }

    function checkCurrentExplorer() public view returns (address) {
        return currentExplorer;
    }

    function getLastAccessTime() public view returns (uint256) {
        return lastAccessTime;
    }

    function toggleExpeditionStatus() public onlyOwner {
        isActive = !isActive;
    }
}
